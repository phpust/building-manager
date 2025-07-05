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
        Schema::create('building_yearly_totals', function (Blueprint $table) {
            $table->id();
            $table->integer('financial_year')->unique();    
            $table->bigInteger('total_deposits_paid');        
            $table->bigInteger('total_deposits_remaining'); 
            $table->bigInteger('total_ending_balance');     
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('building_yearly_totals');
    }
};
