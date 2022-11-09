<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDiscoveryLayerKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function __construct()
    {
        $this->data = [
            ['key' => 'LEGAL_ENTITY_NAME'],
            ['key' => 'LEGAL_ENTITY_IDENTIFIER_LEI'],
            ['key' => 'INCORPORATION_DATE'],
            ['key' => 'INCORPORATION_JURISDICTION'],
            ['key' => 'WEB_DOMAIN'],
            ['key' => 'OPERATING_NAME'],
            ['key' => 'REGULATED'],
            ['key' => 'LICENSE_OR_REGISTRATION_NUMBER'],
            ['key' => 'REGULATION_BODY'],
            ['key' => 'REGULATION_BODY_URL'],
            ['key' => 'CONTACT_COMPLIANCE'],
            ['key' => 'CONTACT_TECHNICAL'],
            ['key' => 'CONTACT_SUPPORT'],
            ['key' => 'CONTACT_LEGAL'],
            ['key' => 'API_URL'],
            ['key' => 'TR_PROTOCOL_CIPHERTRACE'],
            ['key' => 'TR_PROTOCOL_TRISA'],
            ['key' => 'TR_PROTOCOL_TRP'],
            ['key' => 'TR_PROTOCOL_TRUST'],
            ['key' => 'TR_PROTOCOL_SYGNA'],
            ['key' => 'TR_PROTOCOL_VERIFY_VASP'],
            ['key' => 'TR_PROTOCOL_VERISCOPE'],
        ];
    }

    public function up()
    {
        DB::table('discovery_layer_keys')->truncate();
        DB::table('discovery_layer_keys')->insert($this->data);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('discovery_layer_keys')->truncate();
        // orignal keys
        DB::table('discovery_layer_keys')->insert(['key' => 'OPERATING_NAME']);
        DB::table('discovery_layer_keys')->insert(['key' => 'ENTITY']);
        DB::table('discovery_layer_keys')->insert(['key' => 'DOMAIN']);
        DB::table('discovery_layer_keys')->insert(['key' => 'API_URL']);
        DB::table('discovery_layer_keys')->insert(['key' => 'COMPLIANCE_CONTACT']);
        DB::table('discovery_layer_keys')->insert(['key' => 'TECHNOLOGY_CONTACT']);
        DB::table('discovery_layer_keys')->insert(['key' => 'SUPPORT_CONTACT']);
        DB::table('discovery_layer_keys')->insert(['key' => 'JURISDICTION']);
        DB::table('discovery_layer_keys')->insert(['key' => 'REGULATED']);
        DB::table('discovery_layer_keys')->insert(['key' => 'REGULATION_BODY']);
        DB::table('discovery_layer_keys')->insert(['key' => 'REGULATION_BODY_URL']);
        DB::table('discovery_layer_keys')->insert(['key' => 'VIRTUAL_ASSET_VIRTUAL_ASSET']);
        DB::table('discovery_layer_keys')->insert(['key' => 'VIRTUAL_ASSET_FIAT']);
        DB::table('discovery_layer_keys')->insert(['key' => 'FATF_POLICY_URL']);
        DB::table('discovery_layer_keys')->insert(['key' => 'EXCHANGE_OTC']);
        DB::table('discovery_layer_keys')->insert(['key' => 'TIER']);
        DB::table('discovery_layer_keys')->insert(['key' => 'INCORPORATED_DATE']);
    }
}
