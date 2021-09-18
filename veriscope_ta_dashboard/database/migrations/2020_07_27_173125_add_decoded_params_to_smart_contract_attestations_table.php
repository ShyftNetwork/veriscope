<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDecodedParamsToSmartContractAttestationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('smart_contract_attestations', function (Blueprint $table) {
            $table->longText('public_data_decoded')->nullable();
            $table->longText('documents_matrix_encrypted_decoded')->nullable();
            $table->longText('availability_address_encrypted_decoded')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('smart_contract_attestations', function (Blueprint $table) {
            $table->dropColumn('public_data_decoded');
            $table->dropColumn('documents_matrix_encrypted_decoded');
            $table->dropColumn('availability_address_encrypted_decoded');
        });
    }
}
