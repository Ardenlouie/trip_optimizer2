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
        Schema::create('trip_optimizers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->string('control_number')->nullable();
            $table->json('sales_orders')->nullable();
            $table->json('truck_ids')->nullable();
            $table->decimal('total_quantity', 10, 2)->nullable();
            $table->decimal('total_weight', 15, 2)->nullable();
            $table->decimal('total_volume', 15, 2)->nullable();
            $table->decimal('total_pallets', 15, 2)->nullable();
            $table->string('status')->nullable();
            $table->date('date_submitted')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_optimizers');
    }
};
