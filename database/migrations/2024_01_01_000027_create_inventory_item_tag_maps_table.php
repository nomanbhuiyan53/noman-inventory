<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('inventory.tables.item_tag_maps', 'inventory_item_tag_maps'), function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->ulid('item_id')->index();
            $table->ulid('tag_id')->index();

            $table->timestamp('created_at')->useCurrent();

            $table->foreign('item_id')
                ->references('id')
                ->on(config('inventory.tables.items', 'inventory_items'))
                ->cascadeOnDelete();

            $table->foreign('tag_id')
                ->references('id')
                ->on(config('inventory.tables.tags', 'inventory_tags'))
                ->cascadeOnDelete();

            $table->unique(['item_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('inventory.tables.item_tag_maps', 'inventory_item_tag_maps'));
    }
};
