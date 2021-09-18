<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SmartContractAttestationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('smart_contract_attestations', function (Blueprint $table) {
            
            $table->increments('id');
            $table->string('ta_account')->nullable();
            $table->string('jurisdiction')->nullable();
            $table->string('effective_time')->nullable();
            $table->string('expiry_time')->nullable();
            $table->string('public_data')->nullable();
            $table->text('documents_matrix_encrypted')->nullable();
            $table->string('availability_address_encrypted')->nullable();
            $table->string('is_managed')->nullable();
            $table->string('attestation_hash')->nullable();
            $table->string('transaction_hash')->nullable();
            $table->string('user_account')->nullable();
            
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
        Schema::dropIfExists('smart_contract_attestations');
    }
}
