<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIvmsToTrustAnchorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trust_anchors', function (Blueprint $table) {
            $table->text('legal_person_name')->nullable();
            $table->text('legal_person_name_identifier_type')->nullable();
            $table->text('address_type')->nullable();
            $table->text('street_name')->nullable();
            $table->text('building_number')->nullable();
            $table->text('building_name')->nullable();
            $table->text('postcode')->nullable();
            $table->text('town_name')->nullable();
            $table->text('country_sub_division')->nullable();
            $table->text('country')->nullable();
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
            $table->dropColumn('legal_person_name');
            $table->dropColumn('legal_person_name_identifier_type');
            $table->dropColumn('address_type');
            $table->dropColumn('street_name');
            $table->dropColumn('building_number');
            $table->dropColumn('building_name');
            $table->dropColumn('postcode');
            $table->dropColumn('town_name');
            $table->dropColumn('country_sub_division');
            $table->dropColumn('country');
        });
    }
}
