<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trip_optimizer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_optimizer_id')->nullable();
            $table->string('stock_code')->nullable();
            $table->string('item_description')->nullable();
            $table->string('uom')->nullable();
            $table->decimal('quantity', 10, 2)->nullable();
            $table->decimal('weight', 15, 2)->nullable();
            $table->decimal('volume', 15, 2)->nullable();
            $table->decimal('pallets', 15, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_optimizer_items');
    }
};
