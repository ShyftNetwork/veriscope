<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChainalasysToProviders extends Migration
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
                'name' => 'Chainalysis',
                'description' => "We create transparency for a global economy built on blockchains, enabling banks, business, and governments to have a common understanding of how people use cryptocurrency."
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
        DB::table('blockchain_analytics_providers')->where('name', 'Chainalysis')->delete();
    }
}
