<?php

declare(strict_types=1);

namespace Noman\Inventory\Contracts;

use Noman\Inventory\Application\DTOs\AdjustStockDTO;
use Noman\Inventory\Application\DTOs\IssueStockDTO;
use Noman\Inventory\Application\DTOs\OpenStockDTO;
use Noman\Inventory\Application\DTOs\ReceiveStockDTO;
use Noman\Inventory\Application\DTOs\ReserveStockDTO;
use Noman\Inventory\Application\DTOs\ReverseDocumentDTO;
use Noman\Inventory\Application\DTOs\StockDocumentResultDTO;
use Noman\Inventory\Application\DTOs\TransferStockDTO;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;

/**
 * Primary public contract for the inventory engine.
 *
 * This is the single entry point that host applications use to interact
 * with all inventory operations. Behind the interface, each method delegates
 * to a corresponding Action class that handles validation, policy checks,
 * ledger posting, valuation, and event dispatching.
 *
 * Host applications access this via DI or the NomanInventoryFacade:
 *
 *   app(InventoryManagerContract::class)->receive($dto);
 *   NomanInventory::receive($dto);
 */
interface InventoryManagerContract
{
    /**
     * Record an opening balance entry for an item (first-time stock initialisation).
     * Creates a posted document immediately without requiring approval.
     *
     * @throws \Noman\Inventory\Domain\Shared\Exceptions\PolicyViolationException
     * @throws \Noman\Inventory\Domain\Shared\Exceptions\DocumentException
     */
    public function openStock(OpenStockDTO $dto): StockDocumentResultDTO;

    /**
     * Receive stock from an inbound source (purchase, return from customer, production).
     * Creates a GRN-type document and, if no approval is required, posts it immediately.
     *
     * @throws \Noman\Inventory\Domain\Shared\Exceptions\PolicyViolationException
     */
    public function receive(ReceiveStockDTO $dto): StockDocumentResultDTO;

    /**
     * Issue stock for outbound consumption (sale, return to vendor, wastage).
     * Allocates stock using the configured strategy (FEFO/FIFO/Manual).
     *
     * @throws \Noman\Inventory\Domain\Shared\Exceptions\InsufficientStockException
     * @throws \Noman\Inventory\Domain\Shared\Exceptions\PolicyViolationException
     */
    public function issue(IssueStockDTO $dto): StockDocumentResultDTO;

    /**
     * Transfer stock between two locations or warehouses.
     * Creates a paired TransferOut + TransferIn movement.
     *
     * @throws \Noman\Inventory\Domain\Shared\Exceptions\InsufficientStockException
     * @throws \Noman\Inventory\Domain\Shared\Exceptions\PolicyViolationException
     */
    public function transfer(TransferStockDTO $dto): StockDocumentResultDTO;

    /**
     * Post a manual stock adjustment (positive or negative).
     * Typically created after a stock count variance is approved.
     *
     * @throws \Noman\Inventory\Domain\Shared\Exceptions\PolicyViolationException
     */
    public function adjust(AdjustStockDTO $dto): StockDocumentResultDTO;

    /**
     * Soft-reserve a quantity of an item to prevent it from being allocated elsewhere.
     * Returns the reservation ID (UUID/ULID).
     *
     * @throws \Noman\Inventory\Domain\Shared\Exceptions\InsufficientStockException
     * @throws \Noman\Inventory\Domain\Shared\Exceptions\PolicyViolationException
     */
    public function reserve(ReserveStockDTO $dto): string;

    /**
     * Release a previously created reservation, returning the quantity to available stock.
     *
     * @param string $reservationId  The ID returned by reserve()
     */
    public function releaseReservation(string $reservationId): void;

    /**
     * Reverse a posted document by creating compensating ledger entries.
     * The original document is marked as 'reversed'; no rows are deleted.
     *
     * @throws \Noman\Inventory\Domain\Shared\Exceptions\DocumentException
     */
    public function reverseDocument(ReverseDocumentDTO $dto): StockDocumentResultDTO;

    /**
     * Return the current available quantity for an item, optionally scoped
     * to a specific location and/or tenant.
     *
     * @param string      $itemId      Inventory item identifier
     * @param string|null $locationId  Optional location scope
     * @param string|null $tenantId    Optional tenant scope (defaults to current tenant)
     */
    public function getBalance(
        string $itemId,
        ?string $locationId = null,
        ?string $tenantId = null,
    ): Quantity;
}
