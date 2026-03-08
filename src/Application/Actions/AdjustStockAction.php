<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Actions;

use Noman\Inventory\Application\DTOs\AdjustStockDTO;
use Noman\Inventory\Application\DTOs\StockDocumentResultDTO;
use Noman\Inventory\Contracts\DocumentNumberGeneratorContract;
use Noman\Inventory\Contracts\PolicyResolverContract;
use Noman\Inventory\Contracts\StockValuatorContract;
use Noman\Inventory\Contracts\TenantResolverContract;
use Noman\Inventory\Domain\Shared\Enums\DocumentStatus;
use Noman\Inventory\Domain\Shared\Enums\MovementType;
use Noman\Inventory\Domain\Shared\Exceptions\PolicyViolationException;

/**
 * Handles manual stock adjustments (positive or negative).
 *
 * Positive adjustments create AdjustmentIn movements.
 * Negative adjustments create AdjustmentOut movements.
 */
final class AdjustStockAction extends AbstractInventoryAction
{
    public function __construct(
        TenantResolverContract $tenantResolver,
        PolicyResolverContract $policyResolver,
        private readonly StockValuatorContract $valuator,
        DocumentNumberGeneratorContract $docNumberGenerator,
    ) {
        parent::__construct($tenantResolver, $policyResolver, $docNumberGenerator);
    }

    public function execute(AdjustStockDTO $dto): StockDocumentResultDTO
    {
        $tenantId = $this->resolveTenantId($dto->tenantId);
        $policy   = $this->policyResolver->resolveForItem($dto->itemId);

        $requiresApproval = $policy->approvalRequired
            || in_array('adjustment', config('inventory.approval_required_for', []), true);

        // Determine movement type and validate negative stock if adjustment is outward
        $movementType = $dto->quantity->isPositive()
            ? MovementType::AdjustmentIn
            : MovementType::AdjustmentOut;

        if ($movementType === MovementType::AdjustmentOut && ! $policy->allowNegativeStock) {
            // We do not check balance here — the calling UI should show a warning;
            // the balance check will be enforced at the projection level.
            // For strict enforcement uncomment the block below:
            // $balance = (new StockBalanceRepository())->getTotalAvailableQuantity(
            //     $dto->itemId, $dto->warehouseId, $tenantId
            // );
            // if ($dto->quantity->abs()->greaterThan($balance)) {
            //     throw InsufficientStockException::forItem($dto->itemId, $dto->quantity->abs(), $balance);
            // }
        }

        $status    = $requiresApproval ? DocumentStatus::Pending : DocumentStatus::Posted;
        $postedAt  = $requiresApproval ? null : now();
        $docNumber = $this->docNumberGenerator->generate('adjustment', $tenantId);

        $document = $this->createDocument([
            'id'                     => $this->generateId(),
            'tenant_id'              => $tenantId,
            'document_number'        => $docNumber->getValue(),
            'document_type'          => 'adjustment',
            'status'                 => $status->value,
            'source_warehouse_id'    => $dto->warehouseId,
            'source_location_id'     => $dto->locationId,
            'reference_document_number' => $dto->referenceDocNumber,
            'notes'                  => $dto->notes ?? $dto->reason,
            'metadata'               => $dto->metadata ?: null,
            'idempotency_key'        => $dto->idempotencyKey,
            'posted_at'              => $postedAt,
        ]);

        $unitCost = $this->valuator->calculateUnitCost(
            itemId:       $dto->itemId,
            quantity:     $dto->quantity->abs(),
            movementType: $movementType,
            method:       $policy->valuationMethod,
            tenantId:     $tenantId,
        );

        $totalCost = $unitCost->multiply($dto->quantity->getValue());

        $line = $this->createDocumentLine($document, [
            'id'          => $this->generateId(),
            'item_id'     => $dto->itemId,
            'quantity'    => $dto->quantity->getValue(),
            'warehouse_id'=> $dto->warehouseId,
            'location_id' => $dto->locationId,
            'batch_id'    => null, // batchCode resolution could be added here
            'unit_cost'   => $unitCost->getAmount(),
            'total_cost'  => $totalCost->getAmount(),
            'currency'    => $unitCost->getCurrency(),
            'notes'       => $dto->reason,
        ]);

        $movementIds = [];

        if ($status === DocumentStatus::Posted) {
            $movement = $this->createMovement($document, $line, [
                'id'            => $this->generateId(),
                'item_id'       => $dto->itemId,
                'warehouse_id'  => $dto->warehouseId,
                'location_id'   => $dto->locationId,
                'movement_type' => $movementType->value,
                'quantity'      => $dto->quantity->getValue(),
                'unit_cost'     => $unitCost->getAmount(),
                'total_cost'    => $totalCost->getAmount(),
                'currency'      => $unitCost->getCurrency(),
            ]);

            $movementIds[] = $movement->id;

            $this->valuator->recordValuationEntry(
                documentLineId: $line->id,
                itemId:         $dto->itemId,
                quantity:       $dto->quantity->abs(),
                unitCost:       $unitCost,
                movementType:   $movementType,
                method:         $policy->valuationMethod,
                tenantId:       $tenantId,
            );

            event(new \Noman\Inventory\Domain\Inventory\Events\StockAdjusted(
                documentId:     $document->id,
                documentNumber: $docNumber->getValue(),
                itemId:         $dto->itemId,
                quantity:       $dto->quantity->getValue(),
                warehouseId:    $dto->warehouseId,
                reason:         $dto->reason ?? '',
                tenantId:       $tenantId,
            ));
        }

        return new StockDocumentResultDTO(
            documentId:     $document->id,
            documentNumber: $document->document_number,
            status:         $status,
            documentType:   'adjustment',
            tenantId:       $tenantId,
            lineCount:      1,
            movementIds:    $movementIds,
        );
    }
}
