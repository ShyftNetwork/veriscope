<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Constant;

class WebhookToConstantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

      Schema::table('constants', function (Blueprint $table) {
        $table->string('description', 255)->change();
        $table->string('type', 255)->change();
        $table->string('value', 255)->change();

      });


      factory(Constant::class)->create([
          'name'        => 'webhook_url',
          'description' => 'Webhook URL',
          'type'        => 'text',
          'value'       => '',
      ]);

      factory(Constant::class)->create([
          'name'        => 'webhook_secret',
          'description' => 'Webhook Secret',
          'type'        => 'text',
          'value'       => '',
      ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
