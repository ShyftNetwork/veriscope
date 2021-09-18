<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class KycTemplateStatesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('kyc_template_states')->insert(['state' => 'ATTESTATION', 'vasp_type' => 'Beneficiary']);
        DB::table('kyc_template_states')->insert(['state' => 'BENEFICIARY_TA_PUBLIC_KEY', 'vasp_type' => 'Beneficiary']);
        DB::table('kyc_template_states')->insert(['state' => 'BENEFICIARY_USER_PUBLIC_KEY', 'vasp_type' => 'Beneficiary']);
        DB::table('kyc_template_states')->insert(['state' => 'CRYPTO_PUBLIC_KEY', 'vasp_type' => 'Beneficiary']);
        DB::table('kyc_template_states')->insert(['state' => 'CRYPTO_SIGNATURE', 'vasp_type' => 'Beneficiary']);
        DB::table('kyc_template_states')->insert(['state' => 'BENEFICIARY_TA_SIGNATURE', 'vasp_type' => 'Beneficiary']);
        DB::table('kyc_template_states')->insert(['state' => 'BENEFICIARY_USER_SIGNATURE', 'vasp_type' => 'Beneficiary']);
        DB::table('kyc_template_states')->insert(['state' => 'SENDER_TA_PUBLIC_KEY', 'vasp_type' => 'Beneficiary']);
        DB::table('kyc_template_states')->insert(['state' => 'SENDER_USER_PUBLIC_KEY', 'vasp_type' => 'Beneficiary']);
        DB::table('kyc_template_states')->insert(['state' => 'SENDER_TA_SIGNATURE', 'vasp_type' => 'Beneficiary']);
        DB::table('kyc_template_states')->insert(['state' => 'SENDER_USER_SIGNATURE', 'vasp_type' => 'Beneficiary']);
        DB::table('kyc_template_states')->insert(['state' => 'SENDER_KYC', 'vasp_type' => 'Beneficiary']);
        DB::table('kyc_template_states')->insert(['state' => 'BENEFICIARY_KYC', 'vasp_type' => 'Beneficiary']);
        DB::table('kyc_template_states')->insert(['state' => 'DONE']);
    }
}
