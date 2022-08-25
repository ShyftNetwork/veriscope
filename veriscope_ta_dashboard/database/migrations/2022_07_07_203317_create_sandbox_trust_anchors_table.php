<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\SandboxTrustAnchor;

class CreateSandboxTrustAnchorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('sandbox_trust_anchors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ta_prefname')->nullable();
            $table->string('ta_account_type')->nullable();
            $table->string('ta_account_address')->nullable();
            $table->string('ta_public_key')->nullable();
            $table->string('ta_private_key')->nullable();
            $table->longText('ta_signature')->nullable();
            $table->string('ta_signature_hash')->nullable();
            $table->timestamps();
        });

        SandboxTrustAnchor::create([
            'ta_prefname'        => 'PCF Corp',
            'ta_account_type'    => 'BENEFICIARY',
            'ta_account_address' => '0xC0cA43B4848823d5417cAAFB9e8E6704b9d5375c',
            'ta_public_key'      => '0x04c2d213d585fed2213395e61f99b988f692aec84d38b288d14ffc4a90c879531cd92105bf1336da88ec6bf1f86a74293695cfeaa444dc9fcf2614d201e4a64c02',
            'ta_private_key'     => '9d2b80074afbd3069fb9b9640fab06b80236d16ecc2b854d0c1e3a871952731e',
            'ta_signature'       => '{"r":"0x46f872cf316d3dfec32647408cb368fb2d03e99bbd8a96dd6f98548d5ab1e9ab","s":"0x6a2eda3f0fc77a0753d983709a851bb2e66e0d1aa13515a008658f24f252206f","v":"0x25"}',
            'ta_signature_hash'  => '0x0b709dd4809f36a22fe48250b24a5e41e8aea491bace26627f5c68ea9b4fad3f'
        ]);

        SandboxTrustAnchor::create([
            'ta_prefname'        => 'Paycase Inc',
            'ta_account_type'    => 'ORIGINATOR',
            'ta_account_address' => '0xc2106031Dac53b629976e12aF769F60afcB38793',
            'ta_public_key'      => '0x046131efac648ffc1980062b428f532e0b5860dd0c559a853c3e43058bba54e79bab5c03487999f75dfdeacbfd8d5564efe87570fae9a5d309012100d60afd7b37',
            'ta_private_key'     => '36e740045d7573801a604cd740ed9c6c6f4c669ea3ed4ca35205a0c858c3f446',
            'ta_signature'       => '{"r":"0xf5855d2e9b70d6fd0cfb3658c626742c4e3cde5bdea3961ef796b4bb3363b5f3","s":"0x4e88c6ded3e549683bf936fd764e1322debaead339557dd868edcb00503c8a27","v":"0x26"}',
            'ta_signature_hash'  => '0x0b709dd4809f36a22fe48250b24a5e41e8aea491bace26627f5c68ea9b4fad3f'
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('sandbox_trust_anchors');
    }
}
