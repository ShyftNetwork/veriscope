<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(KycDataTableSeeder::class);
        $this->call(KycTemplateStatesTableSeeder::class);
        $this->call(CryptoWalletTypesTableSeeder::class);
        $this->call(CryptoWalletAddressesTableSeeder::class);
        $this->call(DiscoveryLayerKeysTableSeeder::class);
    }
}
