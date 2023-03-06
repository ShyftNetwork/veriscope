<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNationalColumnToTrustAnchorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trust_anchors', function (Blueprint $table) {
            $table->text('postbox')->nullable();
            $table->text('customer_identification')->nullable();
            $table->text('national_identifier')->nullable();
            $table->text('national_identifier_type')->nullable();
            $table->text('country_of_registration')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trust_anchors', function (Blueprint $table) {
            $table->dropColumn('postbox');
            $table->dropColumn('customer_identification');
            $table->dropColumn('national_identifier');
            $table->dropColumn('national_identifier_type');
            $table->dropColumn('country_of_registration');
        });
    }
}
