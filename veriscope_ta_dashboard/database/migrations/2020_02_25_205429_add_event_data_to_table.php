<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEventDataToTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('smart_contract_events', function (Blueprint $table) {
            $table->string('transaction_hash')->nullable();
            $table->string('attestation_hash')->nullable();
            $table->string('user_address')->nullable();
            $table->string('ta_address')->nullable();
            $table->string('event')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('smart_contract_events', function (Blueprint $table) {
            $table->dropColumn('transaction_hash');
            $table->dropColumn('attestation_hash');
            $table->dropColumn('user_address');
            $table->dropColumn('ta_address');
            $table->dropColumn('event');
        });
    }
}
