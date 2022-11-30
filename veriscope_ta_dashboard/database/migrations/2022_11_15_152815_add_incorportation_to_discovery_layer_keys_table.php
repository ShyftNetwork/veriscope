<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIncorportationToDiscoveryLayerKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function __construct()
    {
        $this->data = [
            ['key' => 'INCORPORATION_NUMBER']
        ];
    }

    public function up()
    {
        DB::table('discovery_layer_keys')->insert($this->data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('discovery_layer_keys')->where('key', 'INCORPORATION_NUMBER')->delete();
    }
}
