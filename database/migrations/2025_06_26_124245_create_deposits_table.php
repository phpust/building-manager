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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->enum('payer_type', ['owner', 'tenant']); // مشخص می‌کنه چه کسی پرداخت کرده
            $table->decimal('amount', 14, 0);
            $table->boolean('is_paid')->default(false);
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete(); // متصل به پرداخت
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
