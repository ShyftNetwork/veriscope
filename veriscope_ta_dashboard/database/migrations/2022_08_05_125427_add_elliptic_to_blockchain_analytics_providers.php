<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEllipticToBlockchainAnalyticsProviders extends Migration
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
                'name' => 'Elliptic',
                'description' => "Blockchain analytics, training, and certification for crypto businesses, financial institutions, and regulators.  Manage financial crime risk, achieve regulatory compliance, and grow with confidence.",
                'secret_key_exists' => true,
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
        DB::table('blockchain_analytics_providers')->where('name', 'Elliptic')->delete();

    }
}
