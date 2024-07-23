<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * In laravel 11 need to change the column setting format like below.
     */
    public function up(): void
    {
        Schema::table('kyc_templates', function (Blueprint $table) {
            $table->longText('beneficiary_kyc')->change()->nullable();
            $table->longText('sender_kyc')->change()->nullable();
            $table->longText('beneficiary_user_address_crypto_proof')->change()->nullable();
            $table->longText('sender_user_address_crypto_proof')->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kyc_templates', function (Blueprint $table) {
            $table->longText('beneficiary_kyc')->nullable()->change();
            $table->longText('sender_kyc')->nullable()->change();
            $table->longText('beneficiary_user_address_crypto_proof')->nullable()->change();
            $table->longText('sender_user_address_crypto_proof')->nullable()->change();
        });
    }
};