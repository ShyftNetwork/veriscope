<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditBlockchainAnalyticsProvidersSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('blockchain_analytics_providers', function($table) {
            $table->boolean('secret_key_exists')->nullable()->default(false);
            $table->string('secret_key')->nullable();
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
            $table->dropColumn('secret_key_exists');
            $table->dropColumn('secret_key');
        });
    }
}
