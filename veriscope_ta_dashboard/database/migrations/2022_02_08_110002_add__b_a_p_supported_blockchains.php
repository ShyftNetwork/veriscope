<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBAPSupportedBlockchains extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $data = [
            ['blockchain_analytics_provider_id'=> 1, 'ticker'=> 'btc', 'name' => 'Bitcoin'],
            ['blockchain_analytics_provider_id'=> 1, 'ticker'=> 'bch', 'name' => 'Bitcoin Cash'],
            ['blockchain_analytics_provider_id'=> 1, 'ticker'=> 'ltc', 'name' => 'Litecoin'],
            ['blockchain_analytics_provider_id'=> 1, 'ticker'=> 'eth', 'name' => 'Ethereum'],
            ['blockchain_analytics_provider_id'=> 1, 'ticker'=> 'etc', 'name' => 'Ethereum Classic'],
            ['blockchain_analytics_provider_id'=> 1, 'ticker'=> 'xrp', 'name' => 'XRP'],
            ['blockchain_analytics_provider_id'=> 1, 'ticker'=> 'bsv', 'name' => 'Bitcoin SV'],
            ['blockchain_analytics_provider_id'=> 1, 'ticker'=> 'xlm', 'name' => 'Stellar'],
            ['blockchain_analytics_provider_id'=> 1, 'ticker'=> 'doge', 'name' => 'Dogecoin'],
            ['blockchain_analytics_provider_id'=> 1, 'ticker'=> 'dash', 'name' => 'Dash'],
            ['blockchain_analytics_provider_id'=> 1, 'ticker'=> 'zec', 'name' => 'Zcash'],
            ['blockchain_analytics_provider_id'=> 1, 'ticker'=> 'algo', 'name' => 'Algorand'],
            ['blockchain_analytics_provider_id'=> 1, 'ticker'=> 'trx', 'name' => 'TRON'],
            ['blockchain_analytics_provider_id'=> 1, 'ticker'=> 'eos', 'name' => 'EOS'],
            ['blockchain_analytics_provider_id'=> 1, 'ticker'=> 'sol', 'name' => 'Solana'],
            ['blockchain_analytics_provider_id'=> 1, 'ticker'=> 'matic', 'name' => 'Polygon'],
            ['blockchain_analytics_provider_id'=> 1, 'ticker'=> 'ada', 'name' => 'Cardano'],
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
        DB::table('blockchain_analytics_supported_networks')->where('blockchain_analytics_provider_id', 1)->delete();
    }
}
