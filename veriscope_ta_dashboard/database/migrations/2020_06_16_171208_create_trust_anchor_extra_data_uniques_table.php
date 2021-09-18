<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrustAnchorExtraDataUniquesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trust_anchor_extra_data_uniques', function (Blueprint $table) {
            $table->increments('id');

            $table->string('transaction_hash')->nullable();
            $table->string('trust_anchor_address')->nullable();
            $table->string('key_value_pair_name')->nullable();
            $table->string('key_value_pair_value')->nullable();

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
        Schema::dropIfExists('trust_anchor_extra_data_uniques');
    }
}
