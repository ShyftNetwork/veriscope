<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUserPersonalDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('users', function($table)
      {
          $table->string('middle_name')->nullable();
          $table->dateTime('dob')->nullable();
          $table->string('gender')->default('none');
          $table->string('country_code')->nullable();
          $table->string('telephone')->nullable();
          $table->string('occupation')->nullable();
          $table->string('address')->nullable();
          $table->string('suite')->nullable();
          $table->string('country')->nullable();
          $table->string('state')->nullable();
          $table->string('city')->nullable();
          $table->string('zip')->nullable();
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // These dropsColumns are all seperate because
        // SQLite doesn't support multiple calls to dropColumn / renameColumn in a single modification
        Schema::table('users', function($table) {
          $table->dropColumn('middle_name');
        });
        Schema::table('users', function($table) {
          $table->dropColumn('dob');
        });
        Schema::table('users', function($table) {
          $table->dropColumn('gender');
        });
        Schema::table('users', function($table) {
          $table->dropColumn('country_code');
        });
        Schema::table('users', function($table) {
          $table->dropColumn('telephone');
        });
        Schema::table('users', function($table) {
          $table->dropColumn('occupation');
        });
        Schema::table('users', function($table) {
          $table->dropColumn('address');
        });
        Schema::table('users', function($table) {
          $table->dropColumn('suite');
        });
        Schema::table('users', function($table) {
          $table->dropColumn('country');
        });
        Schema::table('users', function($table) {
          $table->dropColumn('state');
        });
        Schema::table('users', function($table) {
          $table->dropColumn('city');
        });
        Schema::table('users', function($table) {
          $table->dropColumn('zip');
        });
    }
}
