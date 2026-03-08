<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.item_types', 'inventory_item_types'), function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('tenant_id', 64)->nullable()->index();

            $table->string('name');
            $table->string('code', 50)->index();

            // Industry profile drives default policies for items of this type
            $table->string('industry_profile', 50)->default('standard_goods');

            // JSON blob for item-type-level policy overrides applied on top of global config
            $table->json('policy_overrides')->nullable();

            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.item_types', 'inventory_item_types'));
    }
};
