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
      Permission::create(['name' => 'view_shifts']);
      Permission::create(['name' => 'view_team']);
      Permission::create(['name' => 'manage_team']);
      Permission::create(['name' => 'view_locations']);
      Permission::create(['name' => 'manage_locations']);
      Permission::create(['name' => 'view_schedules']);
      Permission::create(['name' => 'manage_schedules']);
      Permission::create(['name' => 'view_assignments']);
      Permission::create(['name' => 'manage_assignments']);
      Permission::create(['name' => 'view_templates']);
      Permission::create(['name' => 'manage_templates']);
      Permission::create(['name' => 'manage_translation']);

      // create roles and assign created permissions
      $role = Role::create(['name' => 'publisher']);
      $role->givePermissionTo('view_shifts');

      $role = Role::create(['name' => 'elder']);
      $role->givePermissionTo('view_team', 'view_locations', 'view_schedules', 'view_assignments', 'view_templates');

      $role = Role::create(['name' => 'team_admin']);
      $role->givePermissionTo('manage_team', 'manage_locations', 'manage_schedules', 'manage_assignments', 'manage_templates');

      $role = Role::create(['name' => 'translator']);
      $role->givePermissionTo('manage_translation');

      $role = Role::create(['name' => 'super_admin']);
      $role->givePermissionTo(Permission::all());
    }
}
