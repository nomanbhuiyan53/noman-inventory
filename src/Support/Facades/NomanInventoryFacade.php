<?php

declare(strict_types=1);

namespace Noman\Inventory\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Noman\Inventory\Application\DTOs\AdjustStockDTO;
use Noman\Inventory\Application\DTOs\IssueStockDTO;
use Noman\Inventory\Application\DTOs\OpenStockDTO;
use Noman\Inventory\Application\DTOs\ReceiveStockDTO;
use Noman\Inventory\Application\DTOs\ReserveStockDTO;
use Noman\Inventory\Application\DTOs\ReverseDocumentDTO;
use Noman\Inventory\Application\DTOs\StockDocumentResultDTO;
use Noman\Inventory\Application\DTOs\TransferStockDTO;
use Noman\Inventory\Contracts\InventoryManagerContract;
use Noman\Inventory\Domain\Shared\ValueObjects\Quantity;

/**
 * Facade for the InventoryManagerContract.
 *
 * Provides a static-style interface to the inventory engine.
 *
 * Usage:
 *   use Noman\Inventory\Support\Facades\NomanInventoryFacade as NomanInventory;
 *
 *   $result = NomanInventory::receive($dto);
 *   $balance = NomanInventory::getBalance($itemId, $locationId);
 *
 * @method static StockDocumentResultDTO openStock(OpenStockDTO $dto)
 * @method static StockDocumentResultDTO receive(ReceiveStockDTO $dto)
 * @method static StockDocumentResultDTO issue(IssueStockDTO $dto)
 * @method static StockDocumentResultDTO transfer(TransferStockDTO $dto)
 * @method static StockDocumentResultDTO adjust(AdjustStockDTO $dto)
 * @method static string                 reserve(ReserveStockDTO $dto)
 * @method static void                   releaseReservation(string $reservationId)
 * @method static StockDocumentResultDTO reverseDocument(ReverseDocumentDTO $dto)
 * @method static Quantity               getBalance(string $itemId, ?string $locationId = null, ?string $tenantId = null)
 */
class NomanInventoryFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return InventoryManagerContract::class;
    }
}
