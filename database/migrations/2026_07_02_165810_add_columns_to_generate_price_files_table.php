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
        Schema::table('generate_price_files', function (Blueprint $table) {
            $table->string('status')->default('Pending');
            $table->string('processed_file')->nullable();
            $table->text('error')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generate_price_files', function (Blueprint $table) {
            //
        });
    }
};
