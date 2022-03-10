<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBAPSupportedBlockchains extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blockchain_analytics_supported_networks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('blockchain_analytics_provider_id')->unsigned()->index();
            $table->foreign('blockchain_analytics_provider_id')->references('id')->on('blockchain_analytics_providers');
            $table->string('ticker')->nullable();
            $table->string('name')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blockchain_analytics_supported_networks');
    }
}
