<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMerklescienceToConstants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $data = [
            ['value'=> '', 'description'=> 'Merkle Science Api Key', 'name' => 'merkle_api_key', 'type' => 'text'],
            ['value'=> '1', 'description'=> 'Run Merkle on Attestations', 'name' => 'merkle_enabled', 'type' => 'boolean']
        ];
        DB::table('constants')->insert($data);
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('constants')->where('name', 'merkle_api_key')->delete();
        DB::table('constants')->where('name', 'merkle_enabled')->delete();
    }
}
