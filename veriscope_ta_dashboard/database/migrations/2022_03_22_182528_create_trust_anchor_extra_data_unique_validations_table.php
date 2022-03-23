<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrustAnchorExtraDataUniqueValidationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trust_anchor_extra_data_unique_validations', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_hash')->nullable();
            $table->string('validator_address')->nullable();
            $table->string('trust_anchor_address')->nullable();
            $table->string('key_value_pair_name')->nullable();
            $table->string('num_validation')->nullable();
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
        Schema::dropIfExists('trust_anchor_extra_data_unique_validations');
    }
}