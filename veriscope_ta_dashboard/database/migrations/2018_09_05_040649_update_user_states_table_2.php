<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUserStatesTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_states', function (Blueprint $table) {
            $table->integer('by_user')->nullable()->after('response');
            $table->boolean('pass')->default('1')->after('by_user');
            $table->text('reason')->nullable()->after('pass');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_states', function (Blueprint $table) {
            $table->dropColumn('by_user');
        });
        Schema::table('user_states', function (Blueprint $table) {
            $table->dropColumn('pass');
        });
        Schema::table('user_states', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
}
