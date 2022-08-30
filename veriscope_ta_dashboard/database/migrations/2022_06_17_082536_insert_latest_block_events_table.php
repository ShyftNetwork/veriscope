<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertLatestBlockEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $data = [
            ['type'=> 'attestations', 'block_number'=> 0],
            ['type'=> 'discoveryLayers', 'block_number'=> 0],
            ['type'=> 'trustAnchors', 'block_number'=> 0]
        ];

        DB::table('latest_block_events')->insert($data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('latest_block_events')->delete();
    }
}
