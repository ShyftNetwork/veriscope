<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoinfirmAvailableNetworks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $data = [
            ['blockchain_analytics_provider_id'=> 3, 'ticker'=> 'btc', 'name' => 'Bitcoin'],
            ['blockchain_analytics_provider_id'=> 3, 'ticker'=> 'eth', 'name' => 'Ethereum'],
            ['blockchain_analytics_provider_id'=> 3, 'ticker'=> 'ltc', 'name' => 'Litecoin'],
            ['blockchain_analytics_provider_id'=> 3, 'ticker'=> 'bch', 'name' => 'Bitcoin Cash'],
            ['blockchain_analytics_provider_id'=> 3, 'ticker'=> 'xrp', 'name' => 'Ripple'],
            ['blockchain_analytics_provider_id'=> 3, 'ticker'=> 'doge', 'name' => 'Dogecoin'],
            ['blockchain_analytics_provider_id'=> 3, 'ticker'=> 'trx', 'name' => 'Tron'],
            ['blockchain_analytics_provider_id'=> 3, 'ticker'=> 'etc', 'name' => 'Ethereum Classic'],
            ['blockchain_analytics_provider_id'=> 3, 'ticker'=> 'xlm', 'name' => 'Stellar'],
            ['blockchain_analytics_provider_id'=> 3, 'ticker'=> 'dash', 'name' => 'Dash'],
            ['blockchain_analytics_provider_id'=> 3, 'ticker'=> 'zec', 'name' => 'Zcash'],
            ['blockchain_analytics_provider_id'=> 3, 'ticker'=> 'xtz', 'name' => 'Tezos'],
            ['blockchain_analytics_provider_id'=> 3, 'ticker'=> 'cro', 'name' => 'Cronos'],
            ['blockchain_analytics_provider_id'=> 3, 'ticker'=> 'ada', 'name' => 'Cardano'],
        ];

        DB::table('blockchain_analytics_supported_networks')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('blockchain_analytics_supported_networks')->where('blockchain_analytics_provider_id', 3)->delete();
    }
}
