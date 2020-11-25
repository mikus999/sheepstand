<?php

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      // create permissions
      $view_shifts = Permission::where('name', 'view_shifts')->first();
      $view_team = Permission::where('name', 'view_team')->first();
      $manage_team = Permission::where('name', 'manage_team')->first();
      $view_locations = Permission::where('name', 'view_locations')->first();
      $manage_locations = Permission::where('name', 'manage_locations')->first();
      $view_schedules = Permission::where('name', 'view_schedules')->first();
      $manage_schedules = Permission::where('name', 'manage_schedules')->first();
      $view_assignments = Permission::where('name', 'view_assignments')->first();
      $manage_assignments = Permission::where('name', 'manage_assignments')->first();
      $view_templates = Permission::where('name', 'view_templates')->first();
      $manage_templates = Permission::where('name', 'manage_templates')->first();
      $manage_translation = Permission::where('name', 'manage_translation')->first();

      // create roles and assign created permissions
      $role = Role::where('name', 'publisher')->first();
      $role->syncPermission($view_shifts);

      $role = Role::where('name', 'elder')->first();
      $role->syncPermissions([$view_shifts, $view_team, $view_locations, $view_schedules, $view_assignments, $view_templates]);

      $role = Role::where('name', 'team_admin')->first();
      $role->syncPermissions([$view_shifts, $view_team, $view_locations, $view_schedules, $view_assignments, $view_templates, 
                                $manage_team, $manage_locations, $manage_schedules, $manage_assignments, $manage_templates]);
      $role = Role::where('name', 'translator')->first();
      $role->syncPermission($manage_translation);

      $role = Role::where('name', 'super_admin')->first();
    }
}
