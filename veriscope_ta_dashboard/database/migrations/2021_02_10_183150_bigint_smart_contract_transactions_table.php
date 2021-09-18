<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BigintSmartContractTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('smart_contract_transactions', function (Blueprint $table) {
            $table->bigInteger('gas')->change();
            $table->bigInteger('value')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('smart_contract_transactions', function (Blueprint $table) {

        $table->integer('gas')->change();
        $table->integer('value')->change();
      });

    }
}
