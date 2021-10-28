<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class CryptoWalletTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('crypto_wallet_types')->insert(['wallet_type' => 'BTC']);
        DB::table('crypto_wallet_types')->insert(['wallet_type' => 'ETH']);
        DB::table('crypto_wallet_types')->insert(['wallet_type' => 'ZEC']);
        DB::table('crypto_wallet_types')->insert(['wallet_type' => 'XMR']);
    }
}
