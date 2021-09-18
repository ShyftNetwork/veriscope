<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBeneficiarySenderUrlsToKycTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kyc_templates', function (Blueprint $table) {
            $table->string('beneficiary_ta_url')->nullable();
            $table->string('sender_ta_url')->nullable();

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
            $table->dropColumn('beneficiary_ta_url');
            $table->dropColumn('sender_ta_url');
        });
    }
}
