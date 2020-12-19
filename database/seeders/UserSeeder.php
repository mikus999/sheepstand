<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Helper\Helper;
use App\Models\User;
use App\Models\Team;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->count(150)->create()->each(function ($user) {
          $team = Team::find(21);
          $user->teams()->attach($team);
          $user->attachRole('publisher', $team);
            
          Helper::addDefaultAvailability($user, true);

        });
    }
}
