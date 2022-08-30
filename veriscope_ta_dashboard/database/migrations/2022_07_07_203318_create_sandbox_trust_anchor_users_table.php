<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\SandboxTrustAnchorUser;

class CreateSandboxTrustAnchorUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('sandbox_trust_anchor_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('prefname')->nullable();
            $table->string('account_address')->nullable();
            $table->string('public_key')->nullable();
            $table->string('private_key')->nullable();
            $table->string('signature_hash')->nullable();
            $table->longText('signature')->nullable();
            $table->json('ivms_data')->nullable();
            $table->integer('sandbox_trust_anchor_id')->unsigned()->index();
            $table->foreign('sandbox_trust_anchor_id')->references('id')->on('sandbox_trust_anchors')->onDelete('cascade');
            $table->timestamps();
        });

        SandboxTrustAnchorUser::create([
            'prefname'        => 'Felix Bailey',
            'account_address' => '0xb532cCA105f966a76C3826451818b55fB2190933',
            'public_key'      => '0x04030d33064a0312133b5c658d6639776c2583f536d683d337dcbef9a7a92b3e948309ed6d539af0be4789f2cb12a7f307b5f3b2bba5691d38b7f22780c7f9cf06',
            'private_key'     => 'e2637b1867c17e44930b9308e6e0d5ddb92388a51572f9464b59b7ca6e0d2343',
            'signature'       => '{"r":"0x44b6fd5ca7bd65df4b63e532783ab9fba32021677bc86d25a19901b2bcc25212","s":"0x2e005a04016ffb0a4be91feb00a91870fe5b82141f6d901d0f949143c05146c2","v":"0x25"}',
            'signature_hash'  => '0x7ec005c40fadb64f4180dcc14d9f5927f649096a08478a4a5a112a3aa77ca549',
            'ivms_data'       => null,
            'sandbox_trust_anchor_id' => 1
        ]);

        SandboxTrustAnchorUser::create([
            'prefname'        => 'Dora Carlson',
            'account_address' => '0xDF122a5c1d5ddE991E2FDC5a5743B30F2a34EA6e',
            'public_key'      => '0x04f0c1de568a05a905951dac793b718a34b38269e4df028c995c2e00c1d64179fadebcbcd8fad4471ebb93f684946f8be8cb9b6087439357eb147f2b7da4a33006',
            'private_key'     => 'da4fc88195068a1fcd3ab03ecf80a475a15fe9faf92ecc38b3a0e2c265c1ddd7',
            'signature'       => '{"r":"0x74f05df791123c5b3ced1df6547d07d6eb8f280a6221e44fa7aa2f9bf6812e44","s":"0x5422e347d5d0c499f5650dda3a69f0162074a4412ee002c20b9c299a341c1876","v":"0x26"}',
            'signature_hash'  => '0x7ec005c40fadb64f4180dcc14d9f5927f649096a08478a4a5a112a3aa77ca549',
            'ivms_data'       => null,
            'sandbox_trust_anchor_id' => 2
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('sandbox_trust_anchor_users');
    }
}
