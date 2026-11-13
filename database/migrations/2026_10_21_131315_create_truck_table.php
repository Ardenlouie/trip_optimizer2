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
        Schema::create('trucks', function (Blueprint $table) {
            $table->id();
            $table->string('truck_name')->nullable();
            $table->decimal('weight_cap', 15, 2)->nullable();
            $table->decimal('volume_cap', 15, 2)->nullable();
            $table->decimal('pallet_cap', 15, 2)->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->string('gasoline')->nullable();
            $table->string('status')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trucks');
    }
};
