<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIvmsToTrustAnchorUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::table('trust_anchor_users', function (Blueprint $table) {
            $table->text('primary_identifier')->nullable();
            $table->text('secondary_identifier')->nullable();
            $table->text('name_identifier_type')->nullable();
            $table->text('address_type')->nullable();
            $table->text('street_name')->nullable();
            $table->text('building_number')->nullable();
            $table->text('postcode')->nullable();
            $table->text('town_name')->nullable();
            $table->text('country_sub_division')->nullable();
            $table->text('country')->nullable();
            $table->text('national_identifier')->nullable();
            $table->text('national_identifier_type')->nullable();
            $table->text('country_of_issue')->nullable();
            $table->text('registration_authority')->nullable();
            $table->text('date_of_birth')->nullable();
            $table->text('place_of_birth')->nullable();
            $table->text('country_of_residence')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trust_anchor_users', function (Blueprint $table) {
            $table->dropColumn('primary_identifier');
            $table->dropColumn('secondary_identifier');
            $table->dropColumn('name_identifier_type');
            $table->dropColumn('address_type');
            $table->dropColumn('street_name');
            $table->dropColumn('building_number');
            $table->dropColumn('postcode');
            $table->dropColumn('town_name');
            $table->dropColumn('country_sub_division');
            $table->dropColumn('country');
            $table->dropColumn('national_identifier');
            $table->dropColumn('national_identifier_type');
            $table->dropColumn('country_of_issue');
            $table->dropColumn('registration_authority');
            $table->dropColumn('date_of_birth');
            $table->dropColumn('place_of_birth');
            $table->dropColumn('country_of_residence');

        });
    }
}
