<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      // Reset cached roles and permissions
      app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

      // create permissions
      Permission::create(['name' => 'manage teams']);
      Permission::create(['name' => 'manage users']);
      Permission::create(['name' => 'manage schedules']);
      Permission::create(['name' => 'manage locations']);
      Permission::create(['name' => 'manage templates']);
      Permission::create(['name' => 'assign shifts']);
      Permission::create(['name' => 'approve shifts']);
      Permission::create(['name' => 'edit translations']);

      // create roles and assign created permissions
      $role = Role::create(['name' => 'team-admin']);
      $role->givePermissionTo('manage users', 'manage schedules', 'manage locations', 'manage templates', 'assign shifts', 'approve shifts');

      $role = Role::create(['name' => 'scheduler']);
      $role->givePermissionTo('manage schedules', 'assign shifts', 'approve shifts');

      $role = Role::create(['name' => 'translator']);
      $role->givePermissionTo('edit translations');

      $role = Role::create(['name' => 'super-admin']);
      $role->givePermissionTo(Permission::all());
    }
}
