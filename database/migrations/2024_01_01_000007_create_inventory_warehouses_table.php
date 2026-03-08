<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.warehouses', 'inventory_warehouses'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->string('name');
            $table->string('code', 50)->index();
            $table->text('address')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('contact_person', 100)->nullable();

            // Virtual warehouses represent logical storage (in-transit, quarantine, production)
            $table->boolean('is_virtual')->default(false);

            $table->boolean('is_active')->default(true)->index();

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.warehouses', 'inventory_warehouses'));
    }
};
