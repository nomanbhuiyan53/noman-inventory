<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Actions;

use Noman\Inventory\Application\DTOs\OpenStockDTO;
use Noman\Inventory\Application\DTOs\StockDocumentResultDTO;
use Noman\Inventory\Contracts\DocumentNumberGeneratorContract;
use Noman\Inventory\Contracts\PolicyResolverContract;
use Noman\Inventory\Contracts\StockValuatorContract;
use Noman\Inventory\Contracts\TenantResolverContract;
use Noman\Inventory\Domain\Shared\Enums\DocumentStatus;
use Noman\Inventory\Domain\Shared\Enums\MovementType;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryBatch;

/**
 * Records an opening balance for an item (first-time stock initialisation).
 *
 * Opening balance documents are always posted immediately.
 * They should only be created once per item/location; subsequent adjustments
 * should use AdjustStockAction.
 */
final class OpenStockAction extends AbstractInventoryAction
{
    public function __construct(
        TenantResolverContract $tenantResolver,
        PolicyResolverContract $policyResolver,
        private readonly StockValuatorContract $valuator,
        DocumentNumberGeneratorContract $docNumberGenerator,
    ) {
        parent::__construct($tenantResolver, $policyResolver, $docNumberGenerator);
    }

    public function execute(OpenStockDTO $dto): StockDocumentResultDTO
    {
        $tenantId  = $this->resolveTenantId($dto->tenantId);
        $policy    = $this->policyResolver->resolveForItem($dto->itemId);
        $docNumber = $this->docNumberGenerator->generate('opening', $tenantId);

        $document = $this->createDocument([
            'id'                       => $this->generateId(),
            'tenant_id'                => $tenantId,
            'document_number'          => $docNumber->getValue(),
            'document_type'            => 'opening',
            'status'                   => DocumentStatus::Posted->value,
            'destination_warehouse_id' => $dto->warehouseId,
            'destination_location_id'  => $dto->locationId,
            'notes'                    => $dto->notes,
            'metadata'                 => $dto->metadata ?: null,
            'posted_at'                => now(),
        ]);

        $batchId = null;

        if ($dto->batchCode) {
            $batch   = InventoryBatch::firstOrCreate(
                [
                    'tenant_id'  => $tenantId,
                    'item_id'    => $dto->itemId,
                    'batch_code' => $dto->batchCode,
                ],
                [
                    'id'          => $this->generateId(),
                    'expiry_date' => $dto->expiryDate,
                    'unit_cost'   => $dto->unitCost?->getAmount(),
                    'currency'    => $dto->unitCost?->getCurrency() ?? config('inventory.currency', 'USD'),
                ]
            );

            $batchId = $batch->id;
        }

        $unitCost  = $this->valuator->calculateUnitCost(
            itemId:       $dto->itemId,
            quantity:     $dto->quantity,
            movementType: MovementType::Opening,
            method:       $policy->valuationMethod,
            inboundCost:  $dto->unitCost,
            batchId:      $batchId,
            tenantId:     $tenantId,
        );

        $totalCost = $unitCost->multiply($dto->quantity->getValue());

        $line = $this->createDocumentLine($document, [
            'id'          => $this->generateId(),
            'item_id'     => $dto->itemId,
            'quantity'    => $dto->quantity->getValue(),
            'warehouse_id'=> $dto->warehouseId,
            'location_id' => $dto->locationId,
            'batch_id'    => $batchId,
            'unit_cost'   => $unitCost->getAmount(),
            'total_cost'  => $totalCost->getAmount(),
            'currency'    => $unitCost->getCurrency(),
            'notes'       => $dto->notes,
        ]);

        $movement = $this->createMovement($document, $line, [
            'id'            => $this->generateId(),
            'item_id'       => $dto->itemId,
            'warehouse_id'  => $dto->warehouseId,
            'location_id'   => $dto->locationId,
            'batch_id'      => $batchId,
            'movement_type' => MovementType::Opening->value,
            'quantity'      => $dto->quantity->getValue(),
            'unit_cost'     => $unitCost->getAmount(),
            'total_cost'    => $totalCost->getAmount(),
            'currency'      => $unitCost->getCurrency(),
        ]);

        $this->valuator->recordValuationEntry(
            documentLineId: $line->id,
            itemId:         $dto->itemId,
            quantity:       $dto->quantity,
            unitCost:       $unitCost,
            movementType:   MovementType::Opening,
            method:         $policy->valuationMethod,
            batchId:        $batchId,
            tenantId:       $tenantId,
        );

        return new StockDocumentResultDTO(
            documentId:     $document->id,
            documentNumber: $document->document_number,
            status:         DocumentStatus::Posted,
            documentType:   'opening',
            tenantId:       $tenantId,
            lineCount:      1,
            movementIds:    [$movement->id],
        );
    }
}
