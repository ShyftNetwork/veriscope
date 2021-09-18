<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sortname', 3);
            $table->string('name', 150)->unique();
            $table->integer('phonecode');
            $table->timestamps();
        });

        // next up, create the countries
        $path = 'app/SqlDumps/countries.sql';
        DB::unprepared(file_get_contents($path));
        DB::unprepared(DB::raw('UPDATE countries SET created_at=\''.Carbon::NOW().'\', updated_at=\''.Carbon::NOW().'\';'));
        //$this->command->info('Country table seeded!');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('countries');
    }
}
