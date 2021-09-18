<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransactionDataToSmartContractTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('smart_contract_transactions', function (Blueprint $table) {
            $table->integer('nonce')->nullable();
            $table->string('block_hash')->nullable();
            $table->integer('block_number')->nullable();
            $table->integer('transaction_index')->nullable();
            $table->string('from_address')->nullable();
            $table->string('to_address')->nullable();
            $table->integer('value')->nullable();
            $table->integer('gas')->nullable();
            $table->integer('gas_price')->nullable();
            $table->longText('payload')->nullable();
            
            
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
            $table->dropColumn('nonce');
            $table->dropColumn('block_hash');
            $table->dropColumn('block_number');
            $table->dropColumn('transaction_index');
            $table->dropColumn('from_address');
            $table->dropColumn('to_address');
            $table->dropColumn('value');
            $table->dropColumn('gas');
            $table->dropColumn('gas_price');
            $table->dropColumn('payload');
        });
    }
}
