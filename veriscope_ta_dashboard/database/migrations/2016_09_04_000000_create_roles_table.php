<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use HttpOz\Roles\Models\Role;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('group')->default('default');
            $table->timestamps();
        });

        // seed the database because it needs to exist
        $roles = [
          [
            'slug' => 'god',
            'name' => 'God Mode',
            'group' => 'admin',
            'description' => 'All features (even hidden) reserved for Nic',
          ],
          [
            'slug' => 'super',
            'name' => 'Super Admin',
            'group' => 'admin',
            'description' => 'A backoffice super user with administrative access to everything',
          ],
          [
            'slug' => 'compliance',
            'name' => 'Compliance Officer',
            'group' => 'admin',
            'description' => 'A backoffice compliance officer role',
          ],
          [
            'slug' => 'support',
            'name' => 'Support',
            'group' => 'admin',
            'description' => 'A backoffice support role',
          ],
          [
            'slug' => 'member',
            'name' => 'Member',
            'group' => 'default',
            'description' => 'A regular member of Shyft Network',
          ],
        ];

        foreach($roles as $role) {
            factory(Role::class)->create([
                'name'  => $role['name'],
                'slug'  => $role['slug'],
                'group' => $role['group'],
                'description' => $role['description'],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
