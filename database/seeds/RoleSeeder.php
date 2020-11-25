<?php

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

      // create permissions
      $view_shifts = Permission::create(['name' => 'view_shifts', 'display_name' => 'View and apply for shifts']);
      $view_team = Permission::create(['name' => 'view_team', 'display_name' => 'View team details, users, options (read-only)']);
      $manage_team = Permission::create(['name' => 'manage_team', 'display_name' => 'Manage team details, users, options']);
      $view_locations = Permission::create(['name' => 'view_locations', 'display_name' => 'View cart locations (read-only)']);
      $manage_locations = Permission::create(['name' => 'manage_locations', 'display_name' => 'Manage cart locations']);
      $view_schedules = Permission::create(['name' => 'view_schedules', 'display_name' => 'View weekly schedules (read-only)']);
      $manage_schedules = Permission::create(['name' => 'manage_schedules', 'display_name' => 'Manage weekly schedules']);
      $view_assignments = Permission::create(['name' => 'view_assignments', 'display_name' => 'View shift assignments (read-only)']);
      $manage_assignments = Permission::create(['name' => 'manage_assignments', 'display_name' => 'Manage shift assignments']);
      $view_templates = Permission::create(['name' => 'view_templates', 'display_name' => 'View schedule templates (read-only)']);
      $manage_templates = Permission::create(['name' => 'manage_templates', 'display_name' => 'Manage schedule templates']);
      $manage_translation = Permission::create(['name' => 'manage_translation', 'display_name' => 'Manage translations']);

      // create roles and assign created permissions
      $role = Role::create(['name' => 'publisher', 'display_name' => 'Publisher']);
      $role->attachPermission($view_shifts);

      $role = Role::create(['name' => 'elder', 'display_name' => 'Elder']);
      $role->attachPermissions([$view_shifts, $view_team, $view_locations, $view_schedules, $view_assignments, $view_templates]);

      $role = Role::create(['name' => 'team_admin', 'display_name' => 'Team Administrator']);
      $role->attachPermissions([$view_shifts, $view_team, $view_locations, $view_schedules, $view_assignments, $view_templates, 
                                $manage_team, $manage_locations, $manage_schedules, $manage_assignments, $manage_templates]);
      $role = Role::create(['name' => 'translator', 'display_name' => 'Translator']);
      $role->attachPermission($manage_translation);

      $role = Role::create(['name' => 'super_admin', 'display_name' => 'Site Administrator']);
    }
}
