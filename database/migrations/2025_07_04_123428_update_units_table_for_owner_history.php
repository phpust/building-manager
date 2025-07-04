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
        Schema::table('units', function (Blueprint $table) {
            $table->integer('floor')->nullable()->after('number');
            $table->text('description')->nullable()->after('number');

            $table->foreignId('current_owner_id')->nullable()->constrained('users');
            $table->foreignId('current_tenant_id')->nullable()->constrained('users');
            
            $table->dropColumn(['owner_name', 'tenant_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('owner_name')->nullable();
            $table->string('tenant_name')->nullable();

            $table->dropConstrainedForeignId('current_owner_id');
            $table->dropConstrainedForeignId('current_tenant_id');
            $table->dropColumn(['floor', 'description']);
        });
    }
};
