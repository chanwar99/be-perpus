<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::where('name', 'owner')->first();

        $user = User::create([
            'name' => "owner",
            'email' => "owner@mail.com",
            'password' => Hash::make("12345678"),
            'role_id' => $role->id,
        ]);
    }
}
