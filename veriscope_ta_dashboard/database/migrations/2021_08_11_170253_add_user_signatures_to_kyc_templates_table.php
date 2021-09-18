<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserSignaturesToKycTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kyc_templates', function (Blueprint $table) {
            $table->string('beneficiary_user_signature_hash')->nullable();
            $table->longText('beneficiary_user_signature')->nullable();
            $table->string('sender_user_signature_hash')->nullable();
            $table->longText('sender_user_signature')->nullable();

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
            $table->dropColumn('beneficiary_user_signature_hash');
            $table->dropColumn('beneficiary_user_signature');
            $table->dropColumn('sender_user_signature_hash');
            $table->dropColumn('sender_user_signature');
        });
    }
}
