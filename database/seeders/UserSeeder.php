<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
                'id' => config('constants.user_ids.casino'),
                'username' => 'admin',
                'name' => 'Admin',
                'email' => 'admin@ejmastnak.com',
                'password' => Hash::make('password'),
                'can_crud' => true,
                'is_admin' => true,
            ]);
            User::updateOrCreate([
                'username' => 'test',
                'name' => 'Test',
                'password' => Hash::make('password'),
                'can_crud' => true,
                'is_admin' => false,
            ]);
        }
        if (\App::environment('production')) {
            User::updateOrCreate([
                'id' => config('constants.user_ids.casino'),
                'username' => 'casino',
                'name' => 'Casino',
                'email' => 'admin@ejmastnak.com',
                'password' => '$2y$10$31p6bH1fmsz3vBkewePWJ.wbXO/SJZL0mCjLmrLM9lwPty.4L50ra',
                'can_crud' => true,
                'is_admin' => true,
            ]);
        }
    }
}
