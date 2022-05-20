<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoveAnalyticsSettingsSettingsFromConstants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       $crystal_api_key = DB::table('constants')->where('name', 'crystal_api_key')->first();
       $crystal_enabled = DB::table('constants')->where('name', 'crystal_enabled')->first();
       $merkle_api_key = DB::table('constants')->where('name', 'merkle_api_key')->first();
       $merkle_enabled = DB::table('constants')->where('name', 'merkle_enabled')->first();

        if ($crystal_api_key->value) DB::table('blockchain_analytics_providers')->where('id', 1)->update(['key' => $crystal_api_key->value, 'enabled' => $crystal_enabled->value]);
        if ($merkle_api_key->value) DB::table('blockchain_analytics_providers')->where('id', 2)->update(['key' => $merkle_api_key->value, 'enabled' => $merkle_enabled->value]);
    
        $crystal_api_key = DB::table('constants')->where('name', 'crystal_api_key')->delete();
        $crystal_enabled = DB::table('constants')->where('name', 'crystal_enabled')->delete();
        $merkle_api_key = DB::table('constants')->where('name', 'merkle_api_key')->delete();
        $merkle_enabled = DB::table('constants')->where('name', 'merkle_enabled')->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $data = [
            ['value'=> '', 'description'=> 'Crystal Api Key', 'name' => 'crystal_api_key', 'type' => 'text'],
            ['value'=> '1', 'description'=> 'Run Crystal on Attestations', 'name' => 'crystal_enabled', 'type' => 'boolean'],
            ['value'=> '', 'description'=> 'Merkle Science Api Key', 'name' => 'merkle_api_key', 'type' => 'text'],
            ['value'=> '1', 'description'=> 'Run Merkle on Attestations', 'name' => 'merkle_enabled', 'type' => 'boolean']
        ];
        DB::table('constants')->insert($data);
    }
}
