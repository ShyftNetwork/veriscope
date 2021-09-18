<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrustAnchorUserCryptoAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trust_anchor_user_crypto_addresses', function (Blueprint $table) {
            $table->increments('id');

            $table->string('crypto_address')->nullable();
            $table->integer('trust_anchor_user_id')->unsigned()->index();
            $table->foreign('trust_anchor_user_id')->references('id')->on('trust_anchor_users')->onDelete('cascade');

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
        Schema::dropIfExists('trust_anchor_user_crypto_addresses');
    }
}
