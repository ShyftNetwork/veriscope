<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlackWebhookToConstants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::table('constants')->where('name', 'auto_invite')->delete();
        DB::table('constants')->where('name', 'maintenance')->delete();

        $data = [
          [
              'name'        => 'slack_webhook_url',
              'description' => 'Slack Webhook URL',
              'type'        => 'text',
              'value'       => '',
          ]
        ];
        DB::table('constants')->insert($data);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('constants')->where('name', 'slack_webhook_url')->delete();
    }
}
