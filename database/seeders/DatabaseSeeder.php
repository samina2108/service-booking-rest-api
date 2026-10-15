<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Demo data for testing the API (safe to run more than once).
     * DEMO ONLY: do not use these passwords on a real/live server.
     */
    public function run()
    {
        // Demo users. "role" is not mass-assignable on purpose, so forceFill is used.
        $users = [
            ['Admin',         'admin@example.com',    'admin'],
            ['Test Customer', 'customer@example.com', 'customer'],
        ];

        foreach ($users as [$name, $email, $role]) {
            $user = User::firstOrNew(['email' => $email]);
            $user->forceFill([
                'name'     => $name,
                'password' => Hash::make('password123'),
                'role'     => $role,
            ])->save();
        }

        // Demo services
        $services = [
            ['Home Cleaning',     'Full home cleaning service',       49.99],
            ['AC Repair',         'Air conditioner repair and service', 39.50],
            ['Plumbing Service',  'Pipe, tap and leakage repair',     29.00],
        ];

        foreach ($services as [$name, $description, $price]) {
            Service::firstOrCreate(
                ['name' => $name],
                ['description' => $description, 'price' => $price, 'is_active' => true]
            );
        }
    }
}
