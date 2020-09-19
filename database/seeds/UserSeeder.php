<?php

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
        factory(App\Models\User::class, 25)->create()->each(function ($user) {
            DB::table('team_user')->insert([
                'user_id' => $user->id,
                'team_id' => 21,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
        });
    }
}
