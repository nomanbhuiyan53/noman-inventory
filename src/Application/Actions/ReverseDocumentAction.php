<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Actions;

use Noman\Inventory\Application\DTOs\ReverseDocumentDTO;
use Noman\Inventory\Application\DTOs\StockDocumentResultDTO;
use Noman\Inventory\Contracts\DocumentNumberGeneratorContract;
use Noman\Inventory\Contracts\StockValuatorContract;
use Noman\Inventory\Contracts\TenantResolverContract;
use Noman\Inventory\Domain\Shared\Enums\DocumentStatus;
use Noman\Inventory\Domain\Shared\Exceptions\DocumentException;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockDocument;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockMovement;

/**
 * Reverses a posted stock document by creating compensating ledger entries.
 *
 * For each original movement row, a new movement row is created with:
 *  - The negated quantity (opposite sign)
 *  - The same item, warehouse, location, batch
 *  - A movement_type derived from the original (e.g. purchase_in → adjustment_out)
 *
 * The original document is marked as 'reversed'. No rows are deleted.
 * A new reversal document is created and linked to the original via reversal_of_id.
 */
final class ReverseDocumentAction
{
    public function __construct(
        private readonly TenantResolverContract $tenantResolver,
        private readonly StockValuatorContract $valuator,
        private readonly DocumentNumberGeneratorContract $docNumberGenerator,
    ) {}

    /**
     * @throws DocumentException
     */
    public function execute(ReverseDocumentDTO $dto): StockDocumentResultDTO
    {
        $tenantId = $dto->tenantId ?? $this->tenantResolver->getCurrentTenantId()?->getValue();

        $original = InventoryStockDocument::query()
            ->forTenant($tenantId)
            ->with(['movements'])
            ->find($dto->documentId);

        if (! $original) {
            throw DocumentException::notFound($dto->documentId);
        }

        if (! $original->canBeReversed()) {
            throw DocumentException::cannotReverse($original->document_number, $original->status->value);
        }

        $docNumber = $this->docNumberGenerator->generate('reversal', $tenantId);

        // Create the reversal document
        $reversalDoc = new InventoryStockDocument();
        $reversalDoc->id                = (string) \Illuminate\Support\Str::ulid();
        $reversalDoc->tenant_id         = $tenantId;
        $reversalDoc->document_number   = $docNumber->getValue();
        $reversalDoc->document_type     = 'reversal';
        $reversalDoc->status            = DocumentStatus::Posted->value;
        $reversalDoc->reversal_of_id    = $original->id;
        $reversalDoc->reversal_reason   = $dto->reason;
        $reversalDoc->notes             = $dto->notes;
        $reversalDoc->posted_at         = now();
        $reversalDoc->save();

        $movementIds = [];

        // Create compensating (negated) movements for each original movement
        foreach ($original->movements as $originalMovement) {
            $compensating = new InventoryStockMovement();
            $compensating->id                      = (string) \Illuminate\Support\Str::ulid();
            $compensating->tenant_id               = $tenantId;
            $compensating->stock_document_id       = $reversalDoc->id;
            $compensating->stock_document_line_id  = $originalMovement->stock_document_line_id;
            $compensating->item_id                 = $originalMovement->item_id;
            $compensating->variant_id              = $originalMovement->variant_id;
            $compensating->warehouse_id            = $originalMovement->warehouse_id;
            $compensating->location_id             = $originalMovement->location_id;
            $compensating->batch_id                = $originalMovement->batch_id;
            $compensating->serial_id               = $originalMovement->serial_id;
            $compensating->unit_id                 = $originalMovement->unit_id;
            $compensating->movement_type           = $originalMovement->movement_type;
            $compensating->quantity                = -$originalMovement->quantity; // negate
            $compensating->unit_cost               = $originalMovement->unit_cost;
            $compensating->total_cost              = $originalMovement->total_cost
                ? -$originalMovement->total_cost
                : null;
            $compensating->currency                = $originalMovement->currency;
            $compensating->reference_document_number = $original->document_number;
            $compensating->notes                   = "Reversal of {$original->document_number}: {$dto->reason}";
            $compensating->posted_at               = now();
            $compensating->save();

            $movementIds[] = $compensating->id;
        }

        // Mark original document as reversed
        $original->status       = DocumentStatus::Reversed->value;
        $original->reversed_at  = now();
        $original->save();

        event(new \Noman\Inventory\Domain\Inventory\Events\DocumentReversed(
            originalDocumentId:   $original->id,
            reversalDocumentId:   $reversalDoc->id,
            reversalDocumentNumber: $docNumber->getValue(),
            reason:               $dto->reason,
            tenantId:             $tenantId,
        ));

        return new StockDocumentResultDTO(
            documentId:     $reversalDoc->id,
            documentNumber: $reversalDoc->document_number,
            status:         DocumentStatus::Posted,
            documentType:   'reversal',
            tenantId:       $tenantId,
            lineCount:      count($movementIds),
            reversalOf:     $original->id,
            movementIds:    $movementIds,
        );
    }
}
