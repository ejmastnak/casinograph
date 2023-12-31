<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder {

    /**
   * Run the database seeds.
   *
   * @return void
   */
    public function run()
    {
        if (\App::environment('local')) {
            User::updateOrCreate([
                'name' => 'admin',
                'email' => 'admin@ejmastnak.com',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // "password"
                'can_create' => true,
                'can_update' => true,
                'can_delete' => true,
                'is_admin' => true,
            ]);
        }
        if (\App::environment('production')) {
            User::updateOrCreate([
                'name' => 'admin',
                'email' => 'admin@ejmastnak.com',
                'password' => '$2y$10$V4ipB2/4kAHrGWEzy62e8egogS63HQGHfwE.GT9noz7PlfIscfb86',
                'can_create' => true,
                'can_update' => true,
                'can_delete' => true,
                'is_admin' => true,
            ]);
        }
    }
}
