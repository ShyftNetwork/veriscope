<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCrystalToBAP extends Migration
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
                'name' => 'Crystal',
                'description' => 'Crystal powers cryptocurrency transaction analysis and monitoring on the blockchain, bringing best-in-class anti-money laundering compliance and risk management solutions to exchanges, banks, and financial institutions.'
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
        DB::table('blockchain_analytics_providers')->where('name', 'Crystal')->delete();
    }
}
