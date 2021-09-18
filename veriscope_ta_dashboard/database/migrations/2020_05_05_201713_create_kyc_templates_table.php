<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKycTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kyc_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('attestation_hash')->nullable();
            $table->string('beneficiary_ta_address')->nullable();
            $table->string('beneficiary_ta_public_key')->nullable();
            $table->string('beneficiary_user_address')->nullable();
            $table->string('beneficiary_user_public_key')->nullable();
            $table->string('beneficiary_ta_signature_hash')->nullable();
            $table->longText('beneficiary_ta_signature')->nullable();
            $table->string('crypto_address_type')->nullable();
            $table->string('crypto_address')->nullable();
            $table->string('crypto_public_key')->nullable();
            $table->string('crypto_signature_hash')->nullable();
            $table->longText('crypto_signature')->nullable();
            $table->string('sender_ta_address')->nullable();
            $table->string('sender_ta_public_key')->nullable();
            $table->string('sender_user_address')->nullable();
            $table->string('sender_user_public_key')->nullable();
            $table->string('sender_ta_signature_hash')->nullable();
            $table->longText('sender_ta_signature')->nullable();
            $table->longText('payload')->nullable();
            $table->string('beneficiary_kyc')->nullable();
            $table->string('sender_kyc')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kyc_templates');
    }
}
