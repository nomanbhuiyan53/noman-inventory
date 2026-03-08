<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Actions;

use Noman\Inventory\Application\DTOs\StockAllocationRequestDTO;
use Noman\Inventory\Application\DTOs\StockDocumentResultDTO;
use Noman\Inventory\Application\DTOs\TransferStockDTO;
use Noman\Inventory\Contracts\DocumentNumberGeneratorContract;
use Noman\Inventory\Contracts\PolicyResolverContract;
use Noman\Inventory\Contracts\StockAllocatorContract;
use Noman\Inventory\Contracts\StockValuatorContract;
use Noman\Inventory\Contracts\TenantResolverContract;
use Noman\Inventory\Domain\Shared\Enums\DocumentStatus;
use Noman\Inventory\Domain\Shared\Enums\MovementType;
use Noman\Inventory\Infrastructure\Persistence\Repositories\StockBalanceRepository;

/**
 * Handles inter-warehouse / inter-location stock transfers.
 *
 * Creates a single document with both TransferOut and TransferIn movements
 * within the same DB transaction, ensuring atomicity.
 */
final class TransferStockAction extends AbstractInventoryAction
{
    public function __construct(
        TenantResolverContract $tenantResolver,
        PolicyResolverContract $policyResolver,
        private readonly StockAllocatorContract $allocator,
        private readonly StockValuatorContract $valuator,
        DocumentNumberGeneratorContract $docNumberGenerator,
    ) {
        parent::__construct($tenantResolver, $policyResolver, $docNumberGenerator);
    }

    public function execute(TransferStockDTO $dto): StockDocumentResultDTO
    {
        $tenantId = $this->resolveTenantId($dto->tenantId);
        $policy   = $this->policyResolver->resolveForItem($dto->itemId);

        $this->validatePolicy(
            policy:      $policy,
            itemId:      $dto->itemId,
            batchCode:   $dto->batchCode,
            expiryDate:  null,
            serialCodes: $dto->serialCodes,
            warehouseId: $dto->fromWarehouseId,
        );

        // Validate available stock at source
        if (! $policy->allowNegativeStock) {
            $balanceRepo = new StockBalanceRepository();
            $available   = $balanceRepo->getTotalAvailableQuantity($dto->itemId, $dto->fromWarehouseId, $tenantId);

            if ($dto->quantity->greaterThan($available)) {
                throw \Noman\Inventory\Domain\Shared\Exceptions\InsufficientStockException::forItem(
                    $dto->itemId,
                    $dto->quantity,
                    $available
                );
            }
        }

        // Allocate from source
        $allocations = $this->allocator->allocate(new StockAllocationRequestDTO(
            itemId:      $dto->itemId,
            quantity:    $dto->quantity,
            warehouseId: $dto->fromWarehouseId,
            strategy:    $policy->allocationStrategy,
            locationId:  $dto->fromLocationId,
            batchCode:   $dto->batchCode,
            serialCodes: $dto->serialCodes,
            tenantId:    $tenantId,
        ));

        $status    = DocumentStatus::Posted;
        $postedAt  = now();
        $docNumber = $this->docNumberGenerator->generate('transfer', $tenantId);

        $document = $this->createDocument([
            'id'                       => $this->generateId(),
            'tenant_id'                => $tenantId,
            'document_number'          => $docNumber->getValue(),
            'document_type'            => 'transfer',
            'status'                   => $status->value,
            'source_warehouse_id'      => $dto->fromWarehouseId,
            'destination_warehouse_id' => $dto->toWarehouseId,
            'source_location_id'       => $dto->fromLocationId,
            'destination_location_id'  => $dto->toLocationId,
            'reference_document_number'=> $dto->referenceDocNumber,
            'notes'                    => $dto->notes,
            'metadata'                 => $dto->metadata ?: null,
            'idempotency_key'          => $dto->idempotencyKey,
            'posted_at'                => $postedAt,
        ]);

        $movementIds = [];

        foreach ($allocations as $allocation) {
            $unitCost  = $this->valuator->calculateUnitCost(
                itemId:       $dto->itemId,
                quantity:     $allocation->allocatedQuantity,
                movementType: MovementType::TransferOut,
                method:       $policy->valuationMethod,
                batchId:      $allocation->batchId,
                tenantId:     $tenantId,
            );

            $totalCost = $unitCost->multiply($allocation->allocatedQuantity->getValue());

            // OUT line (source)
            $outLine = $this->createDocumentLine($document, [
                'id'          => $this->generateId(),
                'item_id'     => $dto->itemId,
                'quantity'    => -$allocation->allocatedQuantity->getValue(),
                'warehouse_id'=> $allocation->warehouseId,
                'location_id' => $allocation->locationId,
                'batch_id'    => $allocation->batchId,
                'unit_cost'   => $unitCost->getAmount(),
                'total_cost'  => -$totalCost->getAmount(),
                'currency'    => $unitCost->getCurrency(),
                'sort_order'  => 0,
            ]);

            $outMovement = $this->createMovement($document, $outLine, [
                'id'            => $this->generateId(),
                'item_id'       => $dto->itemId,
                'warehouse_id'  => $allocation->warehouseId,
                'location_id'   => $allocation->locationId,
                'batch_id'      => $allocation->batchId,
                'movement_type' => MovementType::TransferOut->value,
                'quantity'      => -$allocation->allocatedQuantity->getValue(),
                'unit_cost'     => $unitCost->getAmount(),
                'total_cost'    => -$totalCost->getAmount(),
                'currency'      => $unitCost->getCurrency(),
            ]);

            $movementIds[] = $outMovement->id;

            // IN line (destination)
            $inLine = $this->createDocumentLine($document, [
                'id'          => $this->generateId(),
                'item_id'     => $dto->itemId,
                'quantity'    => $allocation->allocatedQuantity->getValue(),
                'warehouse_id'=> $dto->toWarehouseId,
                'location_id' => $dto->toLocationId,
                'batch_id'    => $allocation->batchId,
                'unit_cost'   => $unitCost->getAmount(),
                'total_cost'  => $totalCost->getAmount(),
                'currency'    => $unitCost->getCurrency(),
                'sort_order'  => 1,
            ]);

            $inMovement = $this->createMovement($document, $inLine, [
                'id'            => $this->generateId(),
                'item_id'       => $dto->itemId,
                'warehouse_id'  => $dto->toWarehouseId,
                'location_id'   => $dto->toLocationId,
                'batch_id'      => $allocation->batchId,
                'movement_type' => MovementType::TransferIn->value,
                'quantity'      => $allocation->allocatedQuantity->getValue(),
                'unit_cost'     => $unitCost->getAmount(),
                'total_cost'    => $totalCost->getAmount(),
                'currency'      => $unitCost->getCurrency(),
            ]);

            $movementIds[] = $inMovement->id;
        }

        event(new \Noman\Inventory\Domain\Inventory\Events\StockTransferred(
            documentId:       $document->id,
            documentNumber:   $docNumber->getValue(),
            itemId:           $dto->itemId,
            quantity:         $dto->quantity->getValue(),
            fromWarehouseId:  $dto->fromWarehouseId,
            toWarehouseId:    $dto->toWarehouseId,
            tenantId:         $tenantId,
        ));

        return new StockDocumentResultDTO(
            documentId:     $document->id,
            documentNumber: $document->document_number,
            status:         $status,
            documentType:   'transfer',
            tenantId:       $tenantId,
            lineCount:      count($movementIds),
            movementIds:    $movementIds,
        );
    }
}
