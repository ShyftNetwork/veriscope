<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMerkleScienceToProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('blockchain_analytics_providers')->insert(
            array(
                'name' => 'Merkle Science',
                'description' => "Merkle Science's Block Monitor is an enterprise-grade, real-time cryptocurrency transaction monitoring and wallet monitoring."
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
        DB::table('blockchain_analytics_providers')->where('name', 'Merkle Science')->delete();
    }
}
