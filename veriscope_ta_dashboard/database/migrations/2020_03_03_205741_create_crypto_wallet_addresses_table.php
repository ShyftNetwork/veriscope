<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCryptoWalletAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crypto_wallet_addresses', function (Blueprint $table) {
            $table->increments('id');

            $table->string('address');
            $table->integer('crypto_wallet_type_id')->unsigned()->index()->nullable();
            $table->foreign('crypto_wallet_type_id')->references('id')->on('crypto_wallet_types')->onDelete('cascade');
            $table->integer('trust_anchor_user_id')->unsigned()->index()->nullable();
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
        Schema::dropIfExists('crypto_wallet_addresses');
    }
}
