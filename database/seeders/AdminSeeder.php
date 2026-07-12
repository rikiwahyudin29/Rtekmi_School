<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan role admin sudah ada dari RoleSeeder
        $this->call(RoleSeeder::class);

        // Buat akun admin default
        $admin = User::updateOrCreate(
            ['username' => 'admin'], // Cari berdasarkan username
            [
                'nama_lengkap' => 'Administrator',
                'email' => 'admin@sekolah.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Assign Role Administrator dan Superadmin
        $roleIds = \App\Models\Role::whereIn('role_key', ['admin', 'superadmin'])->pluck('id');
        
        // Hapus role lama jika ada
        \Illuminate\Support\Facades\DB::table('user_roles')->where('user_id', $admin->id)->delete();
        
        // Masukkan role baru secara manual untuk memastikan masuk ke database
        foreach ($roleIds as $roleId) {
            \Illuminate\Support\Facades\DB::table('user_roles')->insert([
                'user_id' => $admin->id,
                'role_id' => $roleId
            ]);
        }
        
        $this->command->info("Akun admin berhasil dibuat dan diberikan role admin & superadmin!");
    }
}
