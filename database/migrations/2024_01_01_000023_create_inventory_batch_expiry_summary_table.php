<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.batch_expiry_summary', 'inventory_batch_expiry_summary'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->ulid('batch_id')->index();
            $table->ulid('item_id')->index();
            $table->ulid('warehouse_id')->nullable()->index();

            $table->decimal('quantity_on_hand', 18, 4)->default(0);

            $table->date('expiry_date')->index();
            $table->integer('days_until_expiry');
            $table->boolean('is_expired')->default(false)->index();

            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('batch_id')
                ->references('id')
                ->on(config('inventory.tables.batches', 'inventory_batches'))
                ->cascadeOnDelete();

            $table->index(['tenant_id', 'is_expired', 'days_until_expiry'], 'inv_expiry_tenant_exp_days');
            $table->index(['tenant_id', 'item_id', 'expiry_date'], 'inv_expiry_tenant_item_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.batch_expiry_summary', 'inventory_batch_expiry_summary'));
    }
};
