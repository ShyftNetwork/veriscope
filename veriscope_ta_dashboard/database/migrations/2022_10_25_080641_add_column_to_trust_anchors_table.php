<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToTrustAnchorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trust_anchors', function (Blueprint $table) {
            $table->text('department')->nullable()->after('country');
            $table->text('sub_department')->nullable()->after('department');
            $table->text('floor')->nullable()->after('sub_department');
            $table->text('room')->nullable()->after('floor');
            $table->text('town_location_name')->nullable()->after('room');
            $table->text('district_name')->nullable()->after('town_location_name');
            $table->text('address_line')->nullable()->after('district_name');
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
            $table->dropColumn('department');
            $table->dropColumn('sub_department');
            $table->dropColumn('floor');
            $table->dropColumn('room');
            $table->dropColumn('town_location_name');
            $table->dropColumn('district_name');
            $table->dropColumn('address_line');
        });
    }
}
