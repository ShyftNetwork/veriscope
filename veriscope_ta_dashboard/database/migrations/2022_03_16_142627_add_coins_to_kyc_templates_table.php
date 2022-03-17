<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoinsToKycTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kyc_templates', function (Blueprint $table) {
            $table->text('coin_blockchain')->nullable();
            $table->text('coin_token')->nullable();
            $table->text('coin_address')->nullable();
            $table->text('coin_memo')->nullable();
            $table->text('coin_transaction_hash')->nullable();
            $table->text('coin_transaction_value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kyc_templates', function (Blueprint $table) {
            $table->dropColumn('coin_blockchain');
            $table->dropColumn('coin_token');
            $table->dropColumn('coin_address');
            $table->dropColumn('coin_memo');
            $table->dropColumn('coin_transaction_hash');
            $table->dropColumn('coin_transaction_value');
        });
    }
}