<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifySmartContractTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('smart_contract_transactions', function (Blueprint $table) {
            $table->bigInteger('gas_price')->change();
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
        $table->integer('gas_price')->change();
      });

    }
}
