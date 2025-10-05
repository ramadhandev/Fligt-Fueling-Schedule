<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create Users
        $crs = User::create([
            'name' => 'CRS Operator',
            'email' => 'crs@airport.com',
            'password' => Hash::make('password'),
            'role' => 'CRS',
            'phone_number' => '+628123456789',
            'is_whatsapp_opt_in' => true,
        ]);

        $pengawas1 = User::create([
            'name' => 'Pengawas Pagi',
            'email' => 'pengawas.pagi@airport.com',
            'password' => Hash::make('password'),
            'role' => 'Pengawas',
            'phone_number' => '+628123456780',
            'is_whatsapp_opt_in' => true,
        ]);

        $pengawas2 = User::create([
            'name' => 'Pengawas Siang',
            'email' => 'pengawas.siang@airport.com',
            'password' => Hash::make('password'),
            'role' => 'Pengawas',
            'phone_number' => '+628123456781',
            'is_whatsapp_opt_in' => true,
        ]);

        $cro1 = User::create([
            'name' => 'CRO Operator 1',
            'email' => 'cro1@airport.com',
            'password' => Hash::make('password'),
            'role' => 'CRO',
            'phone_number' => '+628123456782',
            'is_whatsapp_opt_in' => true,
        ]);

        $cro2 = User::create([
            'name' => 'CRO Operator 2',
            'email' => 'cro2@airport.com',
            'password' => Hash::make('password'),
            'role' => 'CRO',
            'phone_number' => '+628123456783',
            'is_whatsapp_opt_in' => true,
        ]);

        // Create Shifts
        $shiftPagi = Shift::create([
            'name' => 'Pagi',
            'start_time' => '06:00:00',
            'end_time' => '14:00:00',
            'pengawas_id' => $pengawas1->id,
        ]);

        $shiftSiang = Shift::create([
            'name' => 'Siang',
            'start_time' => '14:00:00',
            'end_time' => '22:00:00',
            'pengawas_id' => $pengawas2->id,
        ]);

        $shiftMalam = Shift::create([
            'name' => 'Malam',
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'pengawas_id' => $pengawas1->id, // Bisa disesuaikan
        ]);
    }
}