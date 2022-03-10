<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockchainAnalyticsAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blockchain_analytics_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('blockchain_analytics_provider_id')->unsigned()->index();
            $table->foreign('blockchain_analytics_provider_id')->references('id')->on('blockchain_analytics_providers');
            $table->string('trust_anchor')->nullable();
            $table->string('user_account')->nullable();
            $table->string('blockchain')->nullable();
            $table->string('crypto_address')->nullable();
            $table->string('custodian')->nullable();
            $table->json('response')->nullable();
            $table->integer('response_status_code')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blockchain_analytics_addresses');
    }
}
