<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrustAnchorUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trust_anchor_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('prefname')->nullable();
            $table->string('password')->nullable();
            $table->string('account_address')->nullable();
            $table->integer('trust_anchor_id')->unsigned()->index();
            $table->foreign('trust_anchor_id')->references('id')->on('trust_anchors')->onDelete('cascade');
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
        Schema::dropIfExists('trust_anchor_users');
    }
}
