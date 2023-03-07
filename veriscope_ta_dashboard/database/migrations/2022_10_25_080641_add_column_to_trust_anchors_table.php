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
            $table->text('department')->nullable();
            $table->text('sub_department')->nullable();
            $table->text('floor')->nullable();
            $table->text('room')->nullable();
            $table->text('town_location_name')->nullable();
            $table->text('district_name')->nullable();
            $table->text('address_line')->nullable();
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
