<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEllipticAvailableNetworks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $data = [
            ['blockchain_analytics_provider_id'=> 5, 'ticker'=> 'algo', 'name' => 'Algorand', 'request_name' => 'algorand'],
            ['blockchain_analytics_provider_id'=> 5, 'ticker'=> 'bch', 'name' => 'Bitcoin Cash', 'request_name' => 'bitcoin_cash'],
            ['blockchain_analytics_provider_id'=> 5, 'ticker'=> 'bnb', 'name' => 'Binance Smart Chain', 'request_name' => 'binance_chain'],
            ['blockchain_analytics_provider_id'=> 5, 'ticker'=> 'btc', 'name' => 'Bitcoin', 'request_name' => 'bitcoin'],
            ['blockchain_analytics_provider_id'=> 5, 'ticker'=> 'dot', 'name' => 'Polkadot', 'request_name' => 'polkadot'],
            ['blockchain_analytics_provider_id'=> 5, 'ticker'=> 'eth', 'name' => 'Ethereum', 'request_name' => 'ethereum'],
            ['blockchain_analytics_provider_id'=> 5, 'ticker'=> 'ltc', 'name' => 'Litecoin', 'request_name' => 'litecoin'],
            ['blockchain_analytics_provider_id'=> 5, 'ticker'=> 'xlm', 'name' => 'Stellar', 'request_name' => 'stellar'],
            ['blockchain_analytics_provider_id'=> 5, 'ticker'=> 'xrp', 'name' => 'Ripple', 'request_name' => 'ripple'],
            ['blockchain_analytics_provider_id'=> 5, 'ticker'=> 'xtz', 'name' => 'Tezos', 'request_name' => 'tezos'],
            ['blockchain_analytics_provider_id'=> 5, 'ticker'=> 'zec', 'name' => 'Zcash', 'request_name' => 'zcash'],
            ['blockchain_analytics_provider_id'=> 5, 'ticker'=> 'zen', 'name' => 'Horizen', 'request_name' => 'horizen'],
            ['blockchain_analytics_provider_id'=> 5, 'ticker'=> 'zil', 'name' => 'Zilliqa', 'request_name' => 'zilliqa']
        ];

        DB::table('blockchain_analytics_supported_networks')->insert($data);    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('blockchain_analytics_supported_networks')->where('blockchain_analytics_provider_id', 5)->delete();
    }
}
