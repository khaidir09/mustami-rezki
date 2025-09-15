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
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->comment('ID Kasir yang mengerjakan');
            $table->date('date');
            $table->string('name');
            $table->integer('quantity');
            $table->decimal('price', 15, 0);
            $table->integer('rate')->comment('Tarif komisi per kancing');
            $table->integer('total_commission');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
