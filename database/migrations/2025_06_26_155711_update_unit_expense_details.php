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
        Schema::table('unit_expense_details', function (Blueprint $table) {
            $table->boolean('is_paid')->default(false)->after('due_date');
            $table->dropColumn('amount_paid');
            $table->dropColumn('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit_expense_details', function (Blueprint $table) {
            $table->decimal('amount_paid', 12, 2)->default(0)->after('amount_due');
            $table->date('due_date')->nullable()->after('amount_due');
            $table->dropColumn('is_paid');
        });
    }
};
