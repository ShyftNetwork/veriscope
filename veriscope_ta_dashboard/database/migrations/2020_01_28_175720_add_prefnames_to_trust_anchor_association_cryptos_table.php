<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrefnamesToTrustAnchorAssociationCryptosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trust_anchor_association_cryptos', function (Blueprint $table) {
            $table->string('sender_account_prefname')->nullable();
            $table->string('receiver_account_prefname')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trust_anchor_association_cryptos', function (Blueprint $table) {
            $table->dropColumn('sender_account_prefname');
            $table->dropColumn('receiver_account_prefname');
        });
    }
}
