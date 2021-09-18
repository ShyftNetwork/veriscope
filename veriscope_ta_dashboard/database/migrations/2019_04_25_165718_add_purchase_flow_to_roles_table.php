<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use HttpOz\Roles\Models\Role;

class AddPurchaseFlowToRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        factory(Role::class)->create([
            'name'  => 'Purchase Flow Access',
            'slug'  => 'purchaseflow',
            'group' => 'default',
            'description' => 'The member has access to the purchase flow in the marketplace',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Role::where('slug', 'purchaseflow')->firstOrFail()->delete();
    }
}
