<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('inventory.tables.categories', 'inventory_categories');

        Schema::create($table, function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            // Self-referencing FK for nested category trees
            $table->ulid('parent_id')->nullable()->index();

            $table->string('name');
            $table->string('code', 50)->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code']);
        });

        // Add self-referencing FK separately (table must exist first)
        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->foreign('parent_id')
                ->references('id')
                ->on(config('inventory.tables.categories', 'inventory_categories'))
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.categories', 'inventory_categories'));
    }
};
