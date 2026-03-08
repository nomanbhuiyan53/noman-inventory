<?php

declare(strict_types=1);

namespace Noman\Inventory\Domain\Shared\Enums;

/**
 * Lifecycle status of an inventory stock document.
 *
 * Status transitions:
 *   draft → pending → approved → posted
 *   draft → cancelled
 *   pending → cancelled
 *   approved → cancelled
 *   posted → reversed
 *
 * Ledger entries are ONLY created when a document reaches the `posted` status.
 * Reversal creates compensating ledger entries; it does NOT delete existing ones.
 */
enum DocumentStatus: string
{
    /** Document is being created and has not been submitted yet */
    case Draft      = 'draft';

    /** Document has been submitted and is awaiting approval */
    case Pending    = 'pending';

    /** Document has been approved and is ready to post */
    case Approved   = 'approved';

    /** Document has been posted; ledger entries exist and stock has moved */
    case Posted     = 'posted';

    /** Document has been reversed via compensating entries; stock has been un-moved */
    case Reversed   = 'reversed';

    /** Document was cancelled before posting; no ledger entries were created */
    case Cancelled  = 'cancelled';

    /**
     * Returns true if the document can still be edited (no ledger impact yet).
     */
    public function isEditable(): bool
    {
        return match ($this) {
            self::Draft, self::Pending => true,
            default                    => false,
        };
    }

    /**
     * Returns true if the document has produced ledger entries.
     */
    public function hasLedgerEntries(): bool
    {
        return match ($this) {
            self::Posted, self::Reversed => true,
            default                      => false,
        };
    }

    /**
     * Returns true if this status is a terminal state (no further transitions).
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::Reversed, self::Cancelled => true,
            default                         => false,
        };
    }

    /**
     * Returns true if this document can be reversed (only posted documents can be).
     */
    public function canBeReversed(): bool
    {
        return $this === self::Posted;
    }

    /**
     * Returns true if this document can be posted.
     */
    public function canBePosted(): bool
    {
        return match ($this) {
            self::Approved, self::Draft => true,
            default                     => false,
        };
    }

    /**
     * Human-readable label for reporting and UI display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft     => 'Draft',
            self::Pending   => 'Pending Approval',
            self::Approved  => 'Approved',
            self::Posted    => 'Posted',
            self::Reversed  => 'Reversed',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Badge color hint for UI rendering (Tailwind-inspired palette names).
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::Draft     => 'gray',
            self::Pending   => 'yellow',
            self::Approved  => 'blue',
            self::Posted    => 'green',
            self::Reversed  => 'orange',
            self::Cancelled => 'red',
        };
    }
}
