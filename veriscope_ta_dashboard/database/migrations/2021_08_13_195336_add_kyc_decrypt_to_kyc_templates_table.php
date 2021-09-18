<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKycDecryptToKycTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kyc_templates', function (Blueprint $table) {
            $table->longText('beneficiary_kyc_decrypt')->nullable();
            $table->longText('sender_kyc_decrypt')->nullable();
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
            $table->dropColumn('beneficiary_kyc_decrypt');
            $table->dropColumn('sender_kyc_decrypt');
        });
    }
}
