<?php

namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            // database/seeders/UserSeeder.php
            User::factory()->create(['email' => 'tikkim@imigrasi.go.id'])
                ->profile()->create(['nama' => 'Admin TIKKIM', 'role' => 'tikkim']);

            User::factory()->create(['email' => 'paspor@imigrasi.go.id'])
                ->profile()->create(['nama' => 'Admin Paspor', 'role' => 'seksi', 'seksi' => 'Paspor']);
    }
}