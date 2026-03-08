<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Actions;

use Illuminate\Support\Str;
use Noman\Inventory\Application\DTOs\ReceiveStockDTO;
use Noman\Inventory\Application\DTOs\StockDocumentResultDTO;
use Noman\Inventory\Contracts\DocumentNumberGeneratorContract;
use Noman\Inventory\Contracts\PolicyResolverContract;
use Noman\Inventory\Contracts\StockValuatorContract;
use Noman\Inventory\Contracts\TenantResolverContract;
use Noman\Inventory\Domain\Shared\Enums\DocumentStatus;
use Noman\Inventory\Domain\Shared\Enums\MovementType;
use Noman\Inventory\Domain\Shared\Exceptions\DocumentException;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryBatch;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventorySerialNumber;

/**
 * Handles inbound stock receipt (Goods Received Note).
 *
 * Processing steps:
 *  1. Resolve tenant context
 *  2. Resolve item policy
 *  3. Check idempotency key (skip if already posted)
 *  4. Validate policy requirements (batch, expiry, serial, location)
 *  5. Create or locate batch record if batchCode is provided
 *  6. Create StockDocument + StockDocumentLine
 *  7. Post immediately (or leave at draft/pending if approval required)
 *  8. Create StockMovement ledger entries
 *  9. Create SerialNumber records if serialCodes are provided
 * 10. Record valuation entry
 * 11. Dispatch StockReceived domain event
 */
final class ReceiveStockAction extends AbstractInventoryAction
{
    public function __construct(
        TenantResolverContract $tenantResolver,
        PolicyResolverContract $policyResolver,
        private readonly StockValuatorContract $valuator,
        DocumentNumberGeneratorContract $docNumberGenerator,
    ) {
        parent::__construct($tenantResolver, $policyResolver, $docNumberGenerator);
    }

    /**
     * @throws \Noman\Inventory\Domain\Shared\Exceptions\PolicyViolationException
     * @throws DocumentException
     */
    public function execute(ReceiveStockDTO $dto): StockDocumentResultDTO
    {
        $tenantId = $this->resolveTenantId($dto->tenantId);

        // Idempotency: if this key was already successfully posted, return the existing result
        if ($dto->idempotencyKey) {
            $existing = \Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockDocument::query()
                ->where('tenant_id', $tenantId)
                ->where('idempotency_key', $dto->idempotencyKey)
                ->first();

            if ($existing) {
                if ($existing->isPosted()) {
                    return new StockDocumentResultDTO(
                        documentId:     $existing->id,
                        documentNumber: $existing->document_number,
                        status:         $existing->status,
                        documentType:   'receive',
                        tenantId:       $tenantId,
                        lineCount:      $existing->lines()->count(),
                        movementIds:    $existing->movements()->pluck('id')->all(),
                    );
                }

                throw DocumentException::idempotencyConflict($dto->idempotencyKey);
            }
        }

        // Resolve policy for this item
        $policy = $this->policyResolver->resolveForItem($dto->itemId);

        // Validate policy requirements
        $this->validatePolicy(
            policy:      $policy,
            itemId:      $dto->itemId,
            batchCode:   $dto->batchCode,
            expiryDate:  $dto->expiryDate,
            serialCodes: $dto->serialCodes,
            warehouseId: $dto->warehouseId,
        );

        // Determine posting status: if approval is required by policy, go to pending
        $requiresApproval = $policy->approvalRequired
            || in_array('receive', config('inventory.approval_required_for', []), true);

        $status    = $requiresApproval ? DocumentStatus::Pending : DocumentStatus::Posted;
        $postedAt  = $requiresApproval ? null : now();
        $docNumber = $this->docNumberGenerator->generate('receive', $tenantId);

        // Create document
        $document = $this->createDocument([
            'id'                    => $this->generateId(),
            'tenant_id'             => $tenantId,
            'document_number'       => $docNumber->getValue(),
            'document_type'         => 'receive',
            'status'                => $status->value,
            'destination_warehouse_id' => $dto->warehouseId,
            'destination_location_id'  => $dto->locationId,
            'reference_document_number'=> $dto->referenceDocNumber,
            'notes'                 => $dto->notes,
            'metadata'              => $dto->metadata ?: null,
            'idempotency_key'       => $dto->idempotencyKey,
            'posted_at'             => $postedAt,
        ]);

        // Find or create batch if a batch code was provided
        $batchId = null;

        if ($dto->batchCode) {
            $batch = InventoryBatch::firstOrCreate(
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

        // Calculate unit cost for valuation
        $unitCost = $this->valuator->calculateUnitCost(
            itemId:       $dto->itemId,
            quantity:     $dto->quantity,
            movementType: MovementType::PurchaseIn,
            method:       $policy->valuationMethod,
            inboundCost:  $dto->unitCost,
            batchId:      $batchId,
            tenantId:     $tenantId,
        );

        $totalCost = $unitCost->multiply($dto->quantity->getValue());

        // Create document line
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

        $movementIds = [];

        // Create ledger movements only if document is being posted immediately
        if ($status === DocumentStatus::Posted) {
            $movement = $this->createMovement($document, $line, [
                'id'               => $this->generateId(),
                'item_id'          => $dto->itemId,
                'warehouse_id'     => $dto->warehouseId,
                'location_id'      => $dto->locationId,
                'batch_id'         => $batchId,
                'movement_type'    => MovementType::PurchaseIn->value,
                'quantity'         => $dto->quantity->getValue(),  // positive = in
                'unit_cost'        => $unitCost->getAmount(),
                'total_cost'       => $totalCost->getAmount(),
                'currency'         => $unitCost->getCurrency(),
                'reference_document_number' => $dto->referenceDocNumber,
            ]);

            $movementIds[] = $movement->id;

            // Create serial number records for each serial code provided
            foreach ($dto->serialCodes as $serialCode) {
                InventorySerialNumber::create([
                    'id'           => $this->generateId(),
                    'tenant_id'    => $tenantId,
                    'item_id'      => $dto->itemId,
                    'batch_id'     => $batchId,
                    'warehouse_id' => $dto->warehouseId,
                    'location_id'  => $dto->locationId,
                    'serial_code'  => $serialCode,
                    'status'       => 'available',
                ]);
            }

            // Record valuation entry
            $this->valuator->recordValuationEntry(
                documentLineId: $line->id,
                itemId:         $dto->itemId,
                quantity:       $dto->quantity,
                unitCost:       $unitCost,
                movementType:   MovementType::PurchaseIn,
                method:         $policy->valuationMethod,
                batchId:        $batchId,
                tenantId:       $tenantId,
            );

            // Dispatch domain event (Phase 5 will add listeners)
            event(new \Noman\Inventory\Domain\Inventory\Events\StockReceived(
                documentId:    $document->id,
                documentNumber:$docNumber->getValue(),
                itemId:        $dto->itemId,
                quantity:      $dto->quantity->getValue(),
                warehouseId:   $dto->warehouseId,
                locationId:    $dto->locationId,
                batchId:       $batchId,
                tenantId:      $tenantId,
            ));
        }

        return new StockDocumentResultDTO(
            documentId:     $document->id,
            documentNumber: $document->document_number,
            status:         $status,
            documentType:   'receive',
            tenantId:       $tenantId,
            lineCount:      1,
            movementIds:    $movementIds,
        );
    }
}
