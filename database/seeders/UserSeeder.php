<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'toliq_ism' => 'Baxtiyorjonov MuhammadJavoxir Jamshid o\'g\'li',
            'tel_nomer' => '901234567',
            'email'     => 'javohir@erkapoy.uz',
            'password'  => Hash::make('Javohir03'), // <-- to'g'ri usul
            'role'      => 'super_admin',
        ]);
    }
}