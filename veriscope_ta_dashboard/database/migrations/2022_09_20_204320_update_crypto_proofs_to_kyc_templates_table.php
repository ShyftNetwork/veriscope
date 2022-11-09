<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\SandboxTrustAnchorUserCryptoAddress;

class UpdateCryptoProofsToKycTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('kyc_templates', function($table) {
          $table->longText('beneficiary_user_address_crypto_proof')->change();
          $table->longText('sender_user_address_crypto_proof')->change();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
