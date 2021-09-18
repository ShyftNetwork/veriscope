<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKycToTrustAnchorAssociationCryptosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trust_anchor_association_cryptos', function (Blueprint $table) {
            $table->dateTime('sender_dob')->nullable();
            $table->string('sender_gender')->nullable();
            $table->integer('sender_jurisdiction')->nullable();
            $table->dateTime('receiver_dob')->nullable();
            $table->string('receiver_gender')->nullable();
            $table->integer('receiver_jurisdiction')->nullable();
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
            $table->dropColumn('sender_dob');
            $table->dropColumn('sender_gender');
            $table->dropColumn('sender_jurisdiction');
            $table->dropColumn('receiver_dob');
            $table->dropColumn('receiver_gender');
            $table->dropColumn('receiver_jurisdiction');
        });
    }
}
