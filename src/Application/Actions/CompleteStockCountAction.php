<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Actions;

use Illuminate\Support\Str;
use Noman\Inventory\Application\DTOs\AdjustStockDTO;
use Noman\Inventory\Contracts\TenantResolverContract;
use Noman\Inventory\Domain\Shared\Enums\DocumentStatus;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockCountEntry;
use Noman\Inventory\Infrastructure\Persistence\Eloquent\InventoryStockCountSession;

/**
 * Completes a stock count session, computes variances, and optionally
 * auto-posts adjustment documents for any counted variances.
 *
 * @see StartStockCountAction
 */
final class CompleteStockCountAction
{
    public function __construct(
        private readonly TenantResolverContract $tenantResolver,
        private readonly AdjustStockAction $adjustStockAction,
    ) {}

    /**
     * @param  string              $sessionId     The in-progress session to complete
     * @param  array               $counts        Array of ['entry_id' => ..., 'counted_quantity' => ...]
     * @param  bool                $autoAdjust    If true, automatically post adjustments for all variances
     * @param  string|null         $completedBy   User reference
     * @param  string|null         $tenantId      Tenant scope
     */
    public function execute(
        string $sessionId,
        array $counts,
        bool $autoAdjust = false,
        ?string $completedBy = null,
        ?string $tenantId = null,
    ): InventoryStockCountSession {
        $tenantId = $tenantId ?? $this->tenantResolver->getCurrentTenantId()?->getValue();

        $session = InventoryStockCountSession::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->findOrFail($sessionId);

        // Apply counted quantities and compute variances
        foreach ($counts as $countData) {
            /** @var InventoryStockCountEntry|null $entry */
            $entry = InventoryStockCountEntry::find($countData['entry_id']);

            if (! $entry || $entry->session_id !== $sessionId) {
                continue;
            }

            $entry->counted_quantity = (float) $countData['counted_quantity'];
            $entry->computeVariance();
            $entry->save();
        }

        // Auto-post adjustments for entries with variances
        if ($autoAdjust) {
            $entries = InventoryStockCountEntry::query()
                ->where('session_id', $sessionId)
                ->whereNotNull('counted_quantity')
                ->get();

            foreach ($entries as $entry) {
                if (! $entry->hasVariance()) {
                    continue;
                }

                $this->adjustStockAction->execute(new AdjustStockDTO(
                    itemId:              $entry->item_id,
                    quantity:            Quantity::of($entry->variance),
                    warehouseId:         $session->warehouse_id,
                    locationId:          $entry->location_id ?? $session->location_id,
                    batchCode:           null,
                    reason:              "Stock count variance — session {$session->session_number}",
                    stockCountSessionId: $sessionId,
                    tenantId:            $tenantId,
                ));
            }
        }

        $session->status       = 'completed';
        $session->completed_at = now();
        $session->completed_by = $completedBy;
        $session->save();

        event(new \Noman\Inventory\Domain\Inventory\Events\StockCountCompleted(
            sessionId:   $session->id,
            warehouseId: $session->warehouse_id,
            tenantId:    $tenantId,
        ));

        return $session;
    }
}
