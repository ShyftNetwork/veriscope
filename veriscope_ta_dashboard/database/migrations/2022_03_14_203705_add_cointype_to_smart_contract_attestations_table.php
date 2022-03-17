<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCointypeToSmartContractAttestationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('smart_contract_attestations', function (Blueprint $table) {
            $table->text('version_code')->nullable();
            $table->text('coin_blockchain')->nullable();
            $table->text('coin_token')->nullable();
            $table->text('coin_address')->nullable();
            $table->text('coin_memo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('smart_contract_attestations', function (Blueprint $table) {
            $table->dropColumn('version_code');
            $table->dropColumn('coin_blockchain');
            $table->dropColumn('coin_token');
            $table->dropColumn('coin_address');
            $table->dropColumn('coin_memo');
        });
    }
}