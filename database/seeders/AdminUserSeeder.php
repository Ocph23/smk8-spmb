<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@spmb.smkn8tikjayapura.sch.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'panitia@smkn8.sch.id'],
            [
                'name' => 'Panitia SPMB',
                'password' => Hash::make('panitia123'),
                'role' => 'panitia',
                'email_verified_at' => now(),
            ]
        );
    }
}
