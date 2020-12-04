<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\User::class, 10)->create()->each(function ($user) {
            DB::table('team_user')->insert([
                'user_id' => $user->id,
                'team_id' => 26,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
        });
    }
}
