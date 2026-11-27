<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CreateAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Hassan Raza',
                'email_verified_at' => now(),
                'password' => bcrypt('123456'),
            ]
        );

        $admin->syncRoles(['admin']);
    }
}
