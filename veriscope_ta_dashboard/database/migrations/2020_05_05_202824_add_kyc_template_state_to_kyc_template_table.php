<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKycTemplateStateToKycTemplateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kyc_templates', function (Blueprint $table) {
            $table->integer('kyc_template_state_id')->unsigned()->index()->nullable();
            $table->foreign('kyc_template_state_id')->references('id')->on('kyc_template_states');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kyc_templates', function (Blueprint $table) {
            $table->dropColumn('kyc_template_state_id');
        });
    }
}
