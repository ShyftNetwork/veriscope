<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Constant;

class CreateConstantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('constants', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 20);
            $table->string('description', 32);
            $table->string('type', 20);
            $table->string('value', 20);
            $table->timestamps();
        });

        factory(Constant::class)->create([
            'name'        => 'maintenance',
            'description' => 'Maintenance Mode',
            'type'        => 'boolean',
            'value'       => '0',
        ]);

        factory(Constant::class)->create([
            'name'        => 'lang',
            'description' => 'Default Languauge',
            'type'        => 'text',
            'value'       => 'en',
        ]);

        factory(Constant::class)->create([
            'name'        => 'sendgrid',
            'description' => 'Sendgrid',
            'type'        => 'boolean',
            'value'       => '1',
        ]);
        
        factory(Constant::class)->create([
            'name'        => 'auto_invite',
            'description' => 'Auto Grant New Requests',
            'type'        => 'boolean',
            'value'       => '1',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('constants');
    }
}
