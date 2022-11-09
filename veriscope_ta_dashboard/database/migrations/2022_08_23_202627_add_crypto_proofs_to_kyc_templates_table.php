<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCryptoProofsToKycTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kyc_templates', function (Blueprint $table) {
            $table->string('beneficiary_user_address_crypto_proof')->nullable();
            $table->boolean('beneficiary_user_address_crypto_proof_status')->nullable();
            $table->string('sender_user_address_crypto_proof')->nullable();
            $table->boolean('sender_user_address_crypto_proof_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kyc_templates', function (Blueprint $table) {
            $table->dropColumn('beneficiary_user_address_crypto_proof');
            $table->dropColumn('beneficiary_user_address_crypto_proof_status');
            $table->dropColumn('sender_user_address_crypto_proof');
            $table->dropColumn('sender_user_address_crypto_proof_status');
        });
    }
}
