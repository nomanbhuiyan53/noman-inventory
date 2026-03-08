<?php

declare(strict_types=1);

namespace Noman\Inventory\Application\Actions;

use Illuminate\Support\Facades\DB;
use Noman\Inventory\Application\DTOs\AdjustStockDTO;
use Noman\Inventory\Application\DTOs\IssueStockDTO;
use Noman\Inventory\Application\DTOs\OpenStockDTO;
use Noman\Inventory\Application\DTOs\ReceiveStockDTO;
use Noman\Inventory\Application\DTOs\ReserveStockDTO;
use Noman\Inventory\Application\DTOs\ReverseDocumentDTO;
use Noman\Inventory\Application\DTOs\StockDocumentResultDTO;
use Noman\Inventory\Application\DTOs\TransferStockDTO;
use Noman\Inventory\Contracts\DocumentNumberGeneratorContract;
use Noman\Inventory\Contracts\InventoryManagerContract;
use Noman\Inventory\Contracts\PolicyResolverContract;
use Noman\Inventory\Contracts\StockAllocatorContract;
use Noman\Inventory\Contracts\StockValuatorContract;
use Noman\Inventory\Contracts\TenantResolverContract;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;

/**
 * Concrete implementation of the InventoryManagerContract.
 *
 * Orchestrates all inventory operations by delegating to specialised
 * Action classes (ReceiveStockAction, IssueStockAction, etc.).
 * Each operation runs inside a DB transaction to guarantee atomicity.
 *
 * The InventoryManager itself is thin — it resolves the tenant context,
 * resolves the item policy, and delegates to the correct Action.
 *
 * Full Action implementations are provided in Phase 4.
 */
final class InventoryManager implements InventoryManagerContract
{
    public function __construct(
        private readonly TenantResolverContract $tenantResolver,
        private readonly PolicyResolverContract $policyResolver,
        private readonly StockAllocatorContract $allocator,
        private readonly StockValuatorContract $valuator,
        private readonly DocumentNumberGeneratorContract $docNumberGenerator,
    ) {}

    public function openStock(OpenStockDTO $dto): StockDocumentResultDTO
    {
        return DB::transaction(function () use ($dto): StockDocumentResultDTO {
            return (new OpenStockAction(
                $this->tenantResolver,
                $this->policyResolver,
                $this->valuator,
                $this->docNumberGenerator,
            ))->execute($dto);
        });
    }

    public function receive(ReceiveStockDTO $dto): StockDocumentResultDTO
    {
        return DB::transaction(function () use ($dto): StockDocumentResultDTO {
            return (new ReceiveStockAction(
                $this->tenantResolver,
                $this->policyResolver,
                $this->valuator,
                $this->docNumberGenerator,
            ))->execute($dto);
        });
    }

    public function issue(IssueStockDTO $dto): StockDocumentResultDTO
    {
        return DB::transaction(function () use ($dto): StockDocumentResultDTO {
            return (new IssueStockAction(
                $this->tenantResolver,
                $this->policyResolver,
                $this->allocator,
                $this->valuator,
                $this->docNumberGenerator,
            ))->execute($dto);
        });
    }

    public function transfer(TransferStockDTO $dto): StockDocumentResultDTO
    {
        return DB::transaction(function () use ($dto): StockDocumentResultDTO {
            return (new TransferStockAction(
                $this->tenantResolver,
                $this->policyResolver,
                $this->allocator,
                $this->valuator,
                $this->docNumberGenerator,
            ))->execute($dto);
        });
    }

    public function adjust(AdjustStockDTO $dto): StockDocumentResultDTO
    {
        return DB::transaction(function () use ($dto): StockDocumentResultDTO {
            return (new AdjustStockAction(
                $this->tenantResolver,
                $this->policyResolver,
                $this->valuator,
                $this->docNumberGenerator,
            ))->execute($dto);
        });
    }

    public function reserve(ReserveStockDTO $dto): string
    {
        return DB::transaction(function () use ($dto): string {
            return (new ReserveStockAction(
                $this->tenantResolver,
                $this->policyResolver,
            ))->execute($dto);
        });
    }

    public function releaseReservation(string $reservationId): void
    {
        DB::transaction(function () use ($reservationId): void {
            (new ReleaseReservationAction(
                $this->tenantResolver,
            ))->execute($reservationId);
        });
    }

    public function reverseDocument(ReverseDocumentDTO $dto): StockDocumentResultDTO
    {
        return DB::transaction(function () use ($dto): StockDocumentResultDTO {
            return (new ReverseDocumentAction(
                $this->tenantResolver,
                $this->valuator,
                $this->docNumberGenerator,
            ))->execute($dto);
        });
    }

    public function getBalance(
        string $itemId,
        ?string $locationId = null,
        ?string $tenantId = null,
    ): Quantity {
        // TODO: In Phase 4, this will query the stock_balances projection table
        // with a fallback to ledger aggregation when the projection is stale.
        return Quantity::zero();
    }
}
