<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMerkleScienceAvailableNetworks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $data = [
            ['blockchain_analytics_provider_id'=> 2, 'ticker'=> 'btc', 'name' => 'Bitcoin', 'provider_network_id' => '0'],
            ['blockchain_analytics_provider_id'=> 2, 'ticker'=> 'eth', 'name' => 'Ethereum', 'provider_network_id' => '1'],
            ['blockchain_analytics_provider_id'=> 2, 'ticker'=> 'ltc', 'name' => 'Litecoin', 'provider_network_id' => '2'],
            ['blockchain_analytics_provider_id'=> 2, 'ticker'=> 'bch', 'name' => 'Bitcoin Cash', 'provider_network_id' => '3'],
            ['blockchain_analytics_provider_id'=> 2, 'ticker'=> 'xrp', 'name' => 'Ripple', 'provider_network_id' => '4'],
            ['blockchain_analytics_provider_id'=> 2, 'ticker'=> 'bsv', 'name' => 'Bitcoin SV', 'provider_network_id' => '5'],
            ['blockchain_analytics_provider_id'=> 2, 'ticker'=> 'doge', 'name' => 'Dogecoin', 'provider_network_id' => '6'],
            ['blockchain_analytics_provider_id'=> 2, 'ticker'=> 'zil', 'name' => 'Zillliqa', 'provider_network_id' => '7'],
            ['blockchain_analytics_provider_id'=> 2, 'ticker'=> 'bnb', 'name' => 'Binance Coin', 'provider_network_id' => '8'],
            ['blockchain_analytics_provider_id'=> 2, 'ticker'=> 'matic', 'name' => 'Matic', 'provider_network_id' => '9'],
            ['blockchain_analytics_provider_id'=> 2, 'ticker'=> 'trx', 'name' => 'Tron', 'provider_network_id' => '10'],

            
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
        DB::table('blockchain_analytics_supported_networks')->where('blockchain_analytics_provider_id', 2)->delete();
    }
}
