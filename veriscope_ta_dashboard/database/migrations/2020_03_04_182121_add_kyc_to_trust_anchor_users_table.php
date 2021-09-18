<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKycToTrustAnchorUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trust_anchor_users', function (Blueprint $table) {
            $table->dateTime('dob')->nullable();
            $table->string('gender')->nullable();
            $table->integer('jurisdiction')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trust_anchor_users', function (Blueprint $table) {
            $table->dropColumn('dob');
            $table->dropColumn('gender');
            $table->dropColumn('jurisdiction');
        });
    }
}
