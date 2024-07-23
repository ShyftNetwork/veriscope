<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * In laravel 11 need to change the column setting format like below.
     */
    public function up(): void
    {
        Schema::table('smart_contract_transactions', function (Blueprint $table) {
            $table->bigInteger('gas_price')->change()->nullable();
            $table->bigInteger('gas')->change()->nullable();
            $table->bigInteger('value')->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('smart_contract_transactions', function (Blueprint $table) {
            $table->bigInteger('gas_price')->nullable()->change();
            $table->bigInteger('gas')->nullable()->change();
            $table->bigInteger('value')->nullable()->change();
        });
    }
};