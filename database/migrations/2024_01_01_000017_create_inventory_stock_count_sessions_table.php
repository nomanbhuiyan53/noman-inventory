<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.stock_count_sessions', 'inventory_stock_count_sessions'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->string('session_number', 64)->index();
            $table->ulid('warehouse_id')->index();
            $table->ulid('location_id')->nullable()->index();

            // draft, in_progress, completed, cancelled
            $table->string('status', 20)->default('draft')->index();

            $table->date('count_date');
            $table->text('notes')->nullable();

            $table->string('created_by', 64)->nullable();
            $table->string('completed_by', 64)->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on(config('inventory.tables.warehouses', 'inventory_warehouses'))
                ->restrictOnDelete();

            $table->unique(['tenant_id', 'session_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.stock_count_sessions', 'inventory_stock_count_sessions'));
    }
};
