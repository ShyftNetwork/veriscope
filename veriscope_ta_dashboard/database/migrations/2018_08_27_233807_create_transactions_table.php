<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->double('value')->default(0);
            $table->double('usd', 20,2)->default(0);
            $table->integer('crypto_address_id')->unsigned()->index();
            //$table->foreign('crypto_address_id')->references('id')->on('crypto_address')->onDelete('cascade');
            $table->integer('shyft_creds')->unsigned()->default(0);
            $table->integer('bonus_creds')->unsigned()->default(0);
            $table->integer('tranche_id')->unsigned()->nullable();
            $table->string('tx_hash')->nullable();
            $table->string('tx_type')->nullable();
            $table->integer('user_id')->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('transactions');
    }
}
