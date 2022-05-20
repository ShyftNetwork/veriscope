<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChainalasysAvailableNetworks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $data = [
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'algo', 'name' => 'Algorand'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'avax', 'name' => 'Avalanche'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'bnb', 'name' => 'Binance Smart Chain'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'btc', 'name' => 'Bitcoin'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'bch', 'name' => 'Bitcoin Cash'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'bsv', 'name' => 'Bitcoin Satoshi Vision'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'celo', 'name' => 'Celo'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'cro', 'name' => 'Cronos'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'dash', 'name' => 'Dash'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'doge', 'name' => 'Dogecoin'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'eos', 'name' => 'EOS'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'eth', 'name' => 'Ethereum'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'etc', 'name' => 'Ethereum Classic'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'ftm', 'name' => 'Fantom'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'ltc', 'name' => 'Litecoin'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'omni', 'name' => 'Omni'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'matic', 'name' => 'Polygon'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'sol', 'name' => 'Solana'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'trx', 'name' => 'Tron'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'xrp', 'name' => 'XRP'],
            ['blockchain_analytics_provider_id'=> 4, 'ticker'=> 'zec', 'name' => 'Zcash']
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
        DB::table('blockchain_analytics_supported_networks')->where('blockchain_analytics_provider_id', 4)->delete();
    }
}
