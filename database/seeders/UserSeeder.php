<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'seller@example.ru'],
            [
                'name' => 'Иван Петров',
                'password' => Hash::make('password'),
            ]
        );
    }
}
