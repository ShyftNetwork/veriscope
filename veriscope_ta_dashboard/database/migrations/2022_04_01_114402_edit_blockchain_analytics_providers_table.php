<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditBlockchainAnalyticsProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blockchain_analytics_providers', function($table) {
            $table->string('key')->nullable();
            $table->string('enabled')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blockchain_analytics_providers', function($table) {
            $table->dropColumn('key');
            $table->dropColumn('enabled');
        });
    }
}
