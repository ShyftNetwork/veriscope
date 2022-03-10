<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDisableEnableCrystalToConstants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('constants')->insert(
            array(
                'value' => '1',
                'description' => 'Run Crystal on Attestations',
                'name' => 'crystal_enabled',
                'type' => 'boolean'
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
        DB::table('constants')->where('name', 'crystal_enabled')->delete();
    }
}
