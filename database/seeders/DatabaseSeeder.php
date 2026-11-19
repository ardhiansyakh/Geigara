<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Tentukan nama tabel ('user' atau 'users')
        $table = Schema::hasTable('user') ? 'user' : 'users';

        if (!Schema::hasTable($table)) {
            return;
        }

        // 2. Deteksi kolom primary / username
        $columns = Schema::getColumnListing($table);
        $userKey = in_array('user', $columns) ? 'user' : (in_array('username', $columns) ? 'username' : (in_array('name', $columns) ? 'name' : null));

        if (!$userKey) {
            return;
        }

        $passwordHash = Hash::make('password123');

        // 3. Cek apakah user admin sudah ada di database
        $existing = DB::table($table)
            ->whereRaw('LOWER(TRIM(' . $userKey . ')) = ?', ['admin'])
            ->first();

        if ($existing) {
            // Jika user admin sudah ada, cukup update passwordnya agar tidak terjadi UNIQUE constraint error
            DB::table($table)
                ->whereRaw('LOWER(TRIM(' . $userKey . ')) = ?', ['admin'])
                ->update(['password' => $passwordHash]);
        } else {
            // Jika belum ada, lakukan insert dengan pengaman try-catch
            try {
                $insertData = [
                    $userKey => 'admin',
                    'password' => $passwordHash,
                ];

                if (in_array('email', $columns)) {
                    $insertData['email'] = 'admin@monitoring.local';
                }

                DB::table($table)->insert($insertData);
            } catch (\Exception $e) {
                // Fallback jika ada bentrok unique index di SQLite
                DB::table($table)
                    ->whereRaw('LOWER(' . $userKey . ') LIKE ?', ['%admin%'])
                    ->update(['password' => $passwordHash]);
            }
        }
    }
}
