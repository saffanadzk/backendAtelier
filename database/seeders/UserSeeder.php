<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrator',
                'email' => 'admin@atelier.local',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'bio' => 'Founding administrator and curation director. Exploring tectonic architecture, slow living, and sculptural spatial design.',
            ],
            [
                'name' => 'Editor',
                'email' => 'editor@atelier.local',
                'password' => Hash::make('password123'),
                'role' => 'editor',
                'bio' => 'Senior editor covering global contemporary architecture, structural timber innovations, and tactile craftsmanship.',
            ],
            [
                'name' => 'Elena Rostova',
                'email' => 'contributor@atelier.local',
                'password' => Hash::make('password123'),
                'role' => 'contributor',
                'bio' => 'Design critic, architectural writer, and photographer based between Kyoto and Zurich.',
            ],
            [
                'name' => 'Marcus Vance',
                'email' => 'reader@atelier.local',
                'password' => Hash::make('password123'),
                'role' => 'reader',
                'bio' => 'Architectural enthusiast and patron of minimalist spatial publications.',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
