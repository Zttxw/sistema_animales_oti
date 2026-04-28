<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles (Spatie)
        $this->call(RolesSeeder::class);

        // 2. Catalogs
        $this->call(CatalogSeeder::class);

        // 3. Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@mascotas.gob.pe'],
            [
                'first_name'        => 'Admin',
                'last_name'         => 'Sistema',
                'identity_document' => '00000001',
                'email'             => 'admin@mascotas.gob.pe',
                'password'          => Hash::make('admin1234'),
                'status'            => 'ACTIVE',
            ]
        );
        $admin->assignRole('ADMIN');
    }
}
