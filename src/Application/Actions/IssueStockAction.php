<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Actions;

use Noman\Inventory\Application\DTOs\IssueStockDTO;
use Noman\Inventory\Application\DTOs\StockAllocationRequestDTO;
use Noman\Inventory\Application\DTOs\StockDocumentResultDTO;
use Noman\Inventory\Contracts\DocumentNumberGeneratorContract;
use Noman\Inventory\Contracts\PolicyResolverContract;
use Noman\Inventory\Contracts\StockAllocatorContract;
use Noman\Inventory\Contracts\StockValuatorContract;
use Noman\Inventory\Contracts\TenantResolverContract;
use Noman\Inventory\Domain\Shared\Enums\DocumentStatus;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventorySerialNumber;
use Noman\Inventory\Infrastructure\Persistence\Repositories\StockBalanceRepository;

/**
 * Handles outbound stock issuance (sales, wastage, consumption, etc.).
 *
 * Processing steps:
 *  1. Resolve tenant context
 *  2. Resolve item policy
 *  3. Validate policy requirements
 *  4. Allocate stock using configured strategy (FEFO/FIFO/Manual)
 *  5. Validate available quantity (check against balance projection)
 *  6. Create StockDocument + StockDocumentLine (one per allocation layer)
 *  7. Create StockMovement rows (signed negative = outbound)
 *  8. Update serial statuses if applicable
 *  9. Record valuation entries
 * 10. Dispatch StockIssued domain event
 */
final class IssueStockAction extends AbstractInventoryAction
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

    public function execute(IssueStockDTO $dto): StockDocumentResultDTO
    {
        $tenantId = $this->resolveTenantId($dto->tenantId);
        $policy   = $this->policyResolver->resolveForItem($dto->itemId);

        $this->validatePolicy(
            policy:      $policy,
            itemId:      $dto->itemId,
            batchCode:   $dto->batchCode,
            expiryDate:  null,
            serialCodes: $dto->serialCodes,
            warehouseId: $dto->warehouseId,
        );

        // Allocate stock layers (FEFO/FIFO/Manual)
        $allocationRequest = new StockAllocationRequestDTO(
            itemId:           $dto->itemId,
            quantity:         $dto->quantity,
            warehouseId:      $dto->warehouseId,
            strategy:         $policy->allocationStrategy,
            locationId:       $dto->locationId,
            batchCode:        $dto->batchCode,
            serialCodes:      $dto->serialCodes,
            excludeExpired:   true,
            excludeReserved:  true,
            tenantId:         $tenantId,
        );

        $allocations = $this->allocator->allocate($allocationRequest);

        // Check available stock if negative stock is disallowed
        if (! $policy->allowNegativeStock) {
            $balanceRepo = new StockBalanceRepository();
            $available   = $balanceRepo->getTotalAvailableQuantity($dto->itemId, $dto->warehouseId, $tenantId);

            if ($dto->quantity->greaterThan($available)) {
                throw \Noman\Inventory\Domain\Shared\Exceptions\InsufficientStockException::forItem(
                    $dto->itemId,
                    $dto->quantity,
                    $available
                );
            }
        }

        $requiresApproval = in_array($dto->movementType->value, config('inventory.approval_required_for', []), true);
        $status           = $requiresApproval ? DocumentStatus::Pending : DocumentStatus::Posted;
        $postedAt         = $requiresApproval ? null : now();
        $docNumber        = $this->docNumberGenerator->generate('issue', $tenantId);

        $document = $this->createDocument([
            'id'                     => $this->generateId(),
            'tenant_id'              => $tenantId,
            'document_number'        => $docNumber->getValue(),
            'document_type'          => 'issue',
            'status'                 => $status->value,
            'source_warehouse_id'    => $dto->warehouseId,
            'source_location_id'     => $dto->locationId,
            'reference_document_number' => $dto->referenceDocNumber,
            'notes'                  => $dto->notes,
            'metadata'               => $dto->metadata ?: null,
            'idempotency_key'        => $dto->idempotencyKey,
            'posted_at'              => $postedAt,
        ]);

        $movementIds = [];

        // Create one line + movement per allocation layer
        foreach ($allocations as $allocation) {
            $unitCost = $this->valuator->calculateUnitCost(
                itemId:       $dto->itemId,
                quantity:     $allocation->allocatedQuantity,
                movementType: $dto->movementType,
                method:       $policy->valuationMethod,
                batchId:      $allocation->batchId,
                tenantId:     $tenantId,
            );

            $totalCost = $unitCost->multiply($allocation->allocatedQuantity->getValue());

            $line = $this->createDocumentLine($document, [
                'id'          => $this->generateId(),
                'item_id'     => $dto->itemId,
                'quantity'    => $allocation->allocatedQuantity->getValue(),
                'warehouse_id'=> $allocation->warehouseId,
                'location_id' => $allocation->locationId,
                'batch_id'    => $allocation->batchId,
                'unit_cost'   => $unitCost->getAmount(),
                'total_cost'  => $totalCost->getAmount(),
                'currency'    => $unitCost->getCurrency(),
            ]);

            if ($status === DocumentStatus::Posted) {
                $movement = $this->createMovement($document, $line, [
                    'id'            => $this->generateId(),
                    'item_id'       => $dto->itemId,
                    'warehouse_id'  => $allocation->warehouseId,
                    'location_id'   => $allocation->locationId,
                    'batch_id'      => $allocation->batchId,
                    'movement_type' => $dto->movementType->value,
                    'quantity'      => -$allocation->allocatedQuantity->getValue(), // negative = outbound
                    'unit_cost'     => $unitCost->getAmount(),
                    'total_cost'    => -$totalCost->getAmount(),
                    'currency'      => $unitCost->getCurrency(),
                    'reference_document_number' => $dto->referenceDocNumber,
                ]);

                $movementIds[] = $movement->id;

                // Mark serialised units as issued
                if ($allocation->serialCode) {
                    InventorySerialNumber::query()
                        ->where('item_id', $dto->itemId)
                        ->where('serial_code', $allocation->serialCode)
                        ->update(['status' => 'issued']);
                }

                $this->valuator->recordValuationEntry(
                    documentLineId: $line->id,
                    itemId:         $dto->itemId,
                    quantity:       $allocation->allocatedQuantity,
                    unitCost:       $unitCost,
                    movementType:   $dto->movementType,
                    method:         $policy->valuationMethod,
                    batchId:        $allocation->batchId,
                    tenantId:       $tenantId,
                );
            }
        }

        if ($status === DocumentStatus::Posted) {
            event(new \Noman\Inventory\Domain\Inventory\Events\StockIssued(
                documentId:     $document->id,
                documentNumber: $docNumber->getValue(),
                itemId:         $dto->itemId,
                quantity:       $dto->quantity->getValue(),
                warehouseId:    $dto->warehouseId,
                movementType:   $dto->movementType->value,
                tenantId:       $tenantId,
            ));
        }

        return new StockDocumentResultDTO(
            documentId:     $document->id,
            documentNumber: $document->document_number,
            status:         $status,
            documentType:   'issue',
            tenantId:       $tenantId,
            lineCount:      count($allocations),
            movementIds:    $movementIds,
        );
    }
}
