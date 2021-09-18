<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUserStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_states', function (Blueprint $table) {
            $table->mediumText('response')->nullable()->after('payload'); //sqlite couldn't have a default(null) with not null so added empty string
            $table->string('from')->default('')->after('transition'); //sqlite couldn't have a default(null) with not null so added empty string
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // these need to be devided into two Schema calls for sqlite
        Schema::table('user_states', function (Blueprint $table) {
            $table->dropColumn('response');
        });

        Schema::table('user_states', function (Blueprint $table) {
            $table->dropColumn('from');
        });
    }
}
