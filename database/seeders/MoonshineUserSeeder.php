<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\Models\MoonshineUserRole;

class MoonshineUserSeeder extends Seeder
{
    public function run(): void
    {
        MoonshineUser::query()->updateOrCreate(
            ['email' => 'antyuhov2@gmail.com'],
            [
                'name' => 'Администратор',
                'password' => Hash::make('mama2miya'),
                'moonshine_user_role_id' => MoonshineUserRole::DEFAULT_ROLE_ID,
            ]
        );
    }
}
