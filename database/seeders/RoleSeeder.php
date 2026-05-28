<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['role_key' => 'superadmin', 'role_name' => 'Super Administrator (Akses Penuh)'],
            ['role_key' => 'admin', 'role_name' => 'Administrator'],
            ['role_key' => 'kepsek', 'role_name' => 'Kepala Sekolah'],
            ['role_key' => 'kurikulum', 'role_name' => 'Waka Kurikulum'],
            ['role_key' => 'kesiswaan', 'role_name' => 'Waka Kesiswaan'],
            ['role_key' => 'sarpras', 'role_name' => 'Sarana Prasarana'],
            ['role_key' => 'bendahara', 'role_name' => 'Bendahara'],
            ['role_key' => 'bk', 'role_name' => 'Guru BK'],
            ['role_key' => 'guru', 'role_name' => 'Guru Mapel'],
            ['role_key' => 'wali_kelas', 'role_name' => 'Wali Kelas'],
            ['role_key' => 'piket', 'role_name' => 'Guru Piket'],
            ['role_key' => 'siswa', 'role_name' => 'Peserta Didik'],
            ['role_key' => 'pic_tabungan', 'role_name' => 'PIC Tabungan'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['role_key' => $role['role_key']],
                ['role_name' => $role['role_name']]
            );
        }
    }
}
