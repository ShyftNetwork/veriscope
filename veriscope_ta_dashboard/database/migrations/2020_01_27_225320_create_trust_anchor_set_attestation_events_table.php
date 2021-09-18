<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrustAnchorSetAttestationEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trust_anchor_set_attestation_events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('attestation_hash')->nullable();
            $table->string('ta_account')->nullable();
            $table->string('user_account')->nullable();
            $table->string('public_data')->nullable();
            $table->text('documents_matrix_encrypted')->nullable();
            $table->string('availability_address_encrypted')->nullable();
            $table->string('jurisdiction')->nullable();
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
        Schema::dropIfExists('trust_anchor_set_attestation_events');
    }
}
