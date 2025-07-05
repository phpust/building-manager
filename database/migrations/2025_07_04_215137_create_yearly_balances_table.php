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
        Schema::create('yearly_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->integer('financial_year');          
            $table->bigInteger('starting_balance')->default(0);  
            $table->bigInteger('starting_deposits_paid')->default(0);   
            $table->bigInteger('starting_deposits_remaining')->default(0);
            $table->bigInteger('ending_balance')->default(0);
            $table->bigInteger('ending_deposits_paid')->default(0);
            $table->bigInteger('ending_deposits_remaining')->default(0);
            $table->timestamps();

            $table->unique(['unit_id', 'financial_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yearly_balances');
    }
};
