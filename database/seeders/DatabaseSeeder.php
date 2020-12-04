<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RoleSeeder::class);
        $this->call(LanguageSeeder::class);

        // Create an admin user
        $user = User::create([
          'name' => 'Admin',
          'email' => 'admin@sheepstand.com',
          'password' => bcrypt('admin'),
          'user_code' => Helper::getUniqueCode(7, 'user', 'U-')
        ]);

        $user->attachRole('super_admin');

        $this->command->info('Super Admin Created: username: admin@sheepstand.com, password: admin');

    }
}
