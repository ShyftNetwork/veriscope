<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrustAnchorAssociationCryptosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trust_anchor_association_cryptos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('crypto_address')->nullable();

            $table->string('sender_account_address')->nullable();
            

            $table->string('receiver_account_address')->nullable();
            

            $table->string('sender_ta_account_address')->nullable();
            $table->string('sender_ta_assoc_prefname')->nullable();

            $table->string('receiver_ta_account_address')->nullable();
            $table->string('receiver_ta_assoc_prefname')->nullable();

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
        Schema::dropIfExists('trust_anchor_association_cryptos');
    }
}
