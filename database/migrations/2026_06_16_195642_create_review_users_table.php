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
        Schema::create('review_users', function (Blueprint $table) {
            $table->id();

            // Google Authentication
            $table->string('google_id')->nullable();

            // User Details
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('phone', 10);
            $table->string('alternate_phone', 10)->nullable();

            // Reward Details
            $table->string('coupon_code')->nullable();

            $table->enum('affiliate_partner', [
                'Yes',
                'No'
            ]);

            $table->enum('experience_source', [
                'google',
                'android',
                'both'
            ]);

            $table->enum('payout_method', [
                'PhonePe',
                'Google Pay',
                'Paytm',
                'UPI ID'
            ]);

            $table->string('payout_value');

            /*
             * Store uploaded screenshot filenames.
             * Example:
             * ["abc.jpg","xyz.png"]
             */
            $table->json('screenshot_paths')->nullable();

            // Optional review status
            $table->enum('status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending');

            // Admin remarks
            $table->text('remarks')->nullable();

            $table->timestamps();

            // Helpful indexes
            $table->index('phone');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_users');
    }
};
