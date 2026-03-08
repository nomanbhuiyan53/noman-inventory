<?php

declare(strict_types=1);

namespace Noman\Inventory\Contracts;

use Noman\Inventory\Application\Queries\BatchExpiryQuery;
use Noman\Inventory\Application\Queries\InventoryAgingQuery;
use Noman\Inventory\Application\Queries\ReservationStatusQuery;
use Noman\Inventory\Application\Queries\StockByLocationQuery;
use Noman\Inventory\Application\Queries\StockCardQuery;
use Noman\Inventory\Application\Queries\StockLedgerQuery;
use Noman\Inventory\Application\Queries\StockOnHandQuery;
use Noman\Inventory\Application\Queries\ValuationSummaryQuery;
use Noman\Inventory\Application\DTOs\BatchExpiryResultDTO;
use Noman\Inventory\Application\DTOs\InventoryAgingResultDTO;
use Noman\Inventory\Application\DTOs\ReservationStatusResultDTO;
use Noman\Inventory\Application\DTOs\StockByLocationResultDTO;
use Noman\Inventory\Application\DTOs\StockCardResultDTO;
use Noman\Inventory\Application\DTOs\StockLedgerResultDTO;
use Noman\Inventory\Application\DTOs\StockOnHandResultDTO;
use Noman\Inventory\Application\DTOs\ValuationSummaryResultDTO;

/**
 * Contract for the inventory reporting / projection query service.
 *
 * All reporting queries return typed DTO collections, never raw Eloquent models,
 * keeping the reporting layer decoupled from the persistence implementation.
 *
 * Implementations read from the projection (read-model) tables for performance,
 * not directly from the ledger, though a fallback ledger-scan implementation
 * can be provided for environments where projections are disabled.
 */
interface InventoryReporterContract
{
    /**
     * Returns current stock on hand, optionally filtered by item, location, or tenant.
     *
     * @return StockOnHandResultDTO[]
     */
    public function getStockOnHand(StockOnHandQuery $query): array;

    /**
     * Returns stock quantities broken down by warehouse/location.
     *
     * @return StockByLocationResultDTO[]
     */
    public function getStockByLocation(StockByLocationQuery $query): array;

    /**
     * Returns the full append-only stock ledger (movement history).
     *
     * @return StockLedgerResultDTO[]
     */
    public function getStockLedger(StockLedgerQuery $query): array;

    /**
     * Returns a stock card (running balance) for a single item at a location.
     *
     * @return StockCardResultDTO[]
     */
    public function getStockCard(StockCardQuery $query): array;

    /**
     * Returns batch/lot expiry information for perishable items.
     *
     * @return BatchExpiryResultDTO[]
     */
    public function getBatchExpiry(BatchExpiryQuery $query): array;

    /**
     * Returns inventory aging (how long stock has been sitting).
     *
     * @return InventoryAgingResultDTO[]
     */
    public function getInventoryAging(InventoryAgingQuery $query): array;

    /**
     * Returns inventory valuation summary per item or overall.
     *
     * @return ValuationSummaryResultDTO[]
     */
    public function getValuationSummary(ValuationSummaryQuery $query): array;

    /**
     * Returns the status of all active or historical reservations.
     *
     * @return ReservationStatusResultDTO[]
     */
    public function getReservationStatus(ReservationStatusQuery $query): array;
}
