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
        Schema::table('weight_vs_couriers', function (Blueprint $table) {
            $table->string('max_discount')->nullable();
            $table->string('other_limitation')->nullable();
            $table->string('complaint_frequency')->nullable();
            $table->string('dealer_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weight_vs_couriers', function (Blueprint $table) {
            $table->dropColumn(['max_discount', 'other_limitation', 'complaint_frequency', 'dealer_name']);
        });
    }
};
