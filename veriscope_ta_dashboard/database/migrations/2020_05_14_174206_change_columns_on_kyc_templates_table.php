<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnsOnKycTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kyc_templates', function (Blueprint $table) {
            $table->string('beneficiary_kyc', 510)->change();
            $table->string('sender_kyc', 510)->change();
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
            $table->string('beneficiary_kyc', 255)->change();
            $table->string('sender_kyc', 510)->change();
        });
    }
}
