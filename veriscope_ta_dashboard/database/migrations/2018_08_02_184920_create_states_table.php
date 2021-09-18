<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use \Carbon\Carbon;

class CreateStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('states', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 30);
            $table->integer('country_id');
            $table->timestamps();
        });

        // next up, create the states
        $path = 'app/SqlDumps/states.sql';
        DB::unprepared(file_get_contents($path));
        //DB::unprepared(DB::raw('UPDATE states SET created_at=NOW(), updated_at=NOW();'));
        DB::unprepared(DB::raw('UPDATE states SET created_at=\''.Carbon::NOW().'\', updated_at=\''.Carbon::NOW().'\';'));
        //$this->command->info('State table seeded!');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('states');
    }
}
