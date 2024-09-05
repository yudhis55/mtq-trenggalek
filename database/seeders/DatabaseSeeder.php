<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Admin',
        //     'email' => 'admin@gmail.com',
        //     'password' => Hash::make('password'),
        // ]);

        User::create([
            'name' => 'Admin MTQ',
            'email' => 'mtq@admin.com',
            'password' => Hash::make('mtqtrenggalekkab'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Kecamatan Bendungan',
            'email' => 'bendungan@mtq.com',
            'password' => Hash::make('bendungan14'),
            'role' => 'user',
            'utusan_id' => 1,
        ]);

        User::create([
            'name' => 'Kecamatan Dongko',
            'email' => 'dongko@mtq.com',
            'password' => Hash::make('dongko13'),
            'role' => 'user',
            'utusan_id' => 2,
        ]);

        User::create([
            'name' => 'Kecamatan Durenan',
            'email' => 'durenan@mtq.com',
            'password' => Hash::make('durenan12'),
            'role' => 'user',
            'utusan_id' => 3,
        ]);

        User::create([
            'name' => 'Kecamatan Gandusari',
            'email' => 'gandusari@mtq.com',
            'password' => Hash::make('gandusari11'),
            'role' => 'user',
            'utusan_id' => 4,
        ]);

        User::create([
            'name' => 'Kecamatan Kampak',
            'email' => 'kampak@mtq.com',
            'password' => Hash::make('kampak10'),
            'role' => 'user',
            'utusan_id' => 5,
        ]);

        User::create([
            'name' => 'Kecamatan Karangan',
            'email' => 'karangan@mtq.com',
            'password' => Hash::make('karangan09'),
            'role' => 'user',
            'utusan_id' => 6,
        ]);

        User::create([
            'name' => 'Kecamatan Munjungan',
            'email' => 'munjungan@mtq.com',
            'password' => Hash::make('munjungan08'),
            'role' => 'user',
            'utusan_id' => 7,
        ]);

        User::create([
            'name' => 'Kecamatan Panggul',
            'email' => 'panggul@mtq.com',
            'password' => Hash::make('panggu07'),
            'role' => 'user',
            'utusan_id' => 8,
        ]);

        User::create([
            'name' => 'Kecamatan Pogalan',
            'email' => 'pogalan@mtq.com',
            'password' => Hash::make('pogalan06'),
            'role' => 'user',
            'utusan_id' => 9,
        ]);

        User::create([
            'name' => 'Kecamatan Pule',
            'email' => 'pule@mtq.com',
            'password' => Hash::make('pule05'),
            'role' => 'user',
            'utusan_id' => 10,
        ]);

        User::create([
            'name' => 'Kecamatan Suruh',
            'email' => 'suruh@mtq.com',
            'password' => Hash::make('suruh04'),
            'role' => 'user',
            'utusan_id' => 11,
        ]);

        User::create([
            'name' => 'Kecamatan Trenggalek',
            'email' => 'trenggalek@mtq.com',
            'password' => Hash::make('trenggalek03'),
            'role' => 'user',
            'utusan_id' => 12,
        ]);

        User::create([
            'name' => 'Kecamatan Tugu',
            'email' => 'tugu@mtq.com',
            'password' => Hash::make('tugu02'),
            'role' => 'user',
            'utusan_id' => 13,
        ]);

        User::create([
            'name' => 'Kecamatan Watulimo',
            'email' => 'watulimo@mtq.com',
            'password' => Hash::make('watulimo01'),
            'role' => 'user',
            'utusan_id' => 14,
        ]);
    }
}
