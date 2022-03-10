<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCrystalToConstantsTable extends Migration
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
                'value' => '',
                'description' => 'Crystal Api Key',
                'name' => 'crystal_api_key',
                'type' => 'text'
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
        DB::table('constants')->where('name', 'crystal_api_key')->delete();
    }
}
