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
        Schema::table('review_users', function (Blueprint $table) {
            $table->string('profile_picture')->nullable();
            $table->dateTime('review_submitted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('review_users', function (Blueprint $table) {
            $table->dropColumn(['profile_picture', 'review_submitted_at']);
        });
    }
};
