<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class DiscoveryLayerKeysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

            DB::table('discovery_layer_keys')->insert(['key' => 'ENTITY']);
            DB::table('discovery_layer_keys')->insert(['key' => 'DOMAIN']);
            DB::table('discovery_layer_keys')->insert(['key' => 'API_URL']);
            DB::table('discovery_layer_keys')->insert(['key' => 'COMPLIANCE_CONTACT']);
            DB::table('discovery_layer_keys')->insert(['key' => 'TECHNOLOGY_CONTACT']);
            DB::table('discovery_layer_keys')->insert(['key' => 'SUPPORT_CONTACT']);
            DB::table('discovery_layer_keys')->insert(['key' => 'JURISDICTION']);
            DB::table('discovery_layer_keys')->insert(['key' => 'REGULATED']);
            DB::table('discovery_layer_keys')->insert(['key' => 'REGULATION_BODY']);
            DB::table('discovery_layer_keys')->insert(['key' => 'REGULATION_BODY_URL']);
            DB::table('discovery_layer_keys')->insert(['key' => 'VIRTUAL_ASSET_VIRTUAL_ASSET']);
            DB::table('discovery_layer_keys')->insert(['key' => 'VIRTUAL_ASSET_FIAT']);
            DB::table('discovery_layer_keys')->insert(['key' => 'FATF_POLICY_URL']);
            DB::table('discovery_layer_keys')->insert(['key' => 'EXCHANGE_OTC']);
            DB::table('discovery_layer_keys')->insert(['key' => 'TIER']);
            DB::table('discovery_layer_keys')->insert(['key' => 'INCORPORATED_DATE']);

    }
}
