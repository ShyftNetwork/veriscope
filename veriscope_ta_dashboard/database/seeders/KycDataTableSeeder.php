<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class KycDataTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        	DB::table('kyc_datas')->insert(['data_name' => 'Has a legal full name']);
        	DB::table('kyc_datas')->insert(['data_name' => 'Has a Government ID']);
        	DB::table('kyc_datas')->insert(['data_name' => 'Has a postal address']);
        	DB::table('kyc_datas')->insert(['data_name' => 'Age over 18 years']);

    }
}
