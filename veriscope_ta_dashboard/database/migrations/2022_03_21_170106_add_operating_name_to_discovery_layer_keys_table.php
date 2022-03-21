<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOperatingNameToDiscoveryLayerKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('discovery_layer_keys')->insert(
            array(
                'key' => 'OPERATING_NAME'
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
        DB::table('discovery_layer_keys')->where('key', 'OPERATING_NAME')->delete();
    }
}