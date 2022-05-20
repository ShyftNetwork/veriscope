<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoinfirmToBlockchainAnalyticsProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('blockchain_analytics_providers')->insert(
            array(
                'name' => 'Coinfirm',
                'description' => "Coinfirm leads the industry in compliance for cryptocurrency, using powerful analytics across the most comprehensive blockchain database."
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('blockchain_analytics_providers')->where('name', 'Coinfirm')->delete();
    }
}
