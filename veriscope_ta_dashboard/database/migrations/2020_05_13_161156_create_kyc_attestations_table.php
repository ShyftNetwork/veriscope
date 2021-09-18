<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKycAttestationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kyc_attestations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('attestation_hash')->nullable();
            $table->string('ta_account')->nullable();
            $table->string('user_account')->nullable();
            $table->string('public_data_decoded')->nullable();
            $table->text('documents_matrix_decoded')->nullable();
            $table->string('availability_address_decoded')->nullable();
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
        Schema::dropIfExists('kyc_attestations');
    }
}
