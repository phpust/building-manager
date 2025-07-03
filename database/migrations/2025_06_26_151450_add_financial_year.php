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
        Schema::table('expenses', function (Blueprint $table) {
            $table->integer('financial_year')->index()->after('date_to');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->integer('financial_year')->index()->after('paid_at');
        });

        Schema::table('deposits', function (Blueprint $table) {
            $table->integer('financial_year')->index()->after('date');
        });

        Schema::table('unit_expense_details', function (Blueprint $table) {
            $table->integer('financial_year')->index()->after('due_date');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('financial_year');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('financial_year');
        });
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn('financial_year');
        });
        Schema::table('unit_expense_details', function (Blueprint $table) {
            $table->dropColumn('financial_year');
        });

    }
};
