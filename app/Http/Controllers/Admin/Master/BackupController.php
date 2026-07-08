<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    public function index()
    {
        $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $dbName = 'Tables_in_' . env('DB_DATABASE', 'mariyadhulja_db');
        
        $tableList = [];
        foreach ($tables as $table) {
            $tableList[] = $table->{$dbName} ?? array_values((array)$table)[0];
        }

        return Inertia::render('Admin/Master/Backup/Index', [
            'tables' => $tableList
        ]);
    }

    public function database()
    {
        ini_set('max_execution_time', 0); 
        ini_set('memory_limit', '512M');

        $tablesObj = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $dbName = 'Tables_in_' . env('DB_DATABASE', 'mariyadhulja_db');
        $tables = [];
        foreach ($tablesObj as $table) {
            $tables[] = $table->{$dbName} ?? array_values((array)$table)[0];
        }

        $sql = "-- SIAKAD LARAVEL DATABASE BACKUP\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n"; 

        foreach ($tables as $table) {
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $createTableQuery = (array) \Illuminate\Support\Facades\DB::select("SHOW CREATE TABLE `$table`")[0];
            $sql .= array_values($createTableQuery)[1] . ";\n\n";

            $rows = \Illuminate\Support\Facades\DB::table($table)->get();
            if ($rows->count() > 0) {
                foreach ($rows as $row) {
                    $sql .= "INSERT INTO `$table` VALUES(";
                    $values = [];
                    foreach ((array)$row as $val) {
                        if (is_null($val)) {
                            $values[] = "NULL";
                        } else {
                            $escaped = addslashes($val);
                            // handle newlines for SQL
                            $escaped = str_replace(["\n", "\r"], ["\\n", "\\r"], $escaped);
                            $values[] = "'" . $escaped . "'";
                        }
                    }
                    $sql .= implode(", ", $values) . ");\n";
                }
                $sql .= "\n\n";
            }
        }
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = 'Backup_SIAKAD_' . date('Y-m-d_H-i-s') . '.sql';
        
        return response($sql, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function restore(Request $request)
    {
        $request->validate([
            'file_sql' => 'required|file'
        ]);

        $file = $request->file('file_sql');

        if ($file->getClientOriginalExtension() != 'sql') {
            return redirect()->back()->with('error', 'Gagal! Pastikan file yang diunggah berformat .sql');
        }

        $lines = file($file->getRealPath());
        $templine = '';
        
        \Illuminate\Support\Facades\DB::statement("SET FOREIGN_KEY_CHECKS=0;");
        
        foreach ($lines as $line) {
            if (substr($line, 0, 2) == '--' || trim($line) == '') {
                continue;
            }

            $templine .= $line;

            if (substr(trim($line), -1, 1) == ';') {
                try {
                    \Illuminate\Support\Facades\DB::unprepared($templine);
                } catch (\Exception $e) {
                    // skip error
                }
                $templine = '';
            }
        }

        \Illuminate\Support\Facades\DB::statement("SET FOREIGN_KEY_CHECKS=1;");

        return redirect()->back()->with('success', 'Luar Biasa! Database berhasil di-restore ke versi sebelumnya.');
    }

    public function hapusData(Request $request)
    {
        $tables = $request->tables;

        if (empty($tables)) {
            return redirect()->back()->with('error', 'Tidak ada tabel yang dipilih untuk dibersihkan!');
        }

        \Illuminate\Support\Facades\DB::statement("SET FOREIGN_KEY_CHECKS=0;");

        $validTablesObj = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $validTables = [];
        foreach ($validTablesObj as $t) {
            $validTables[] = array_values((array)$t)[0];
        }

        foreach ($tables as $table) {
            if (!in_array($table, $validTables)) {
                continue; // Skip invalid tables to prevent SQL Injection
            }
            if ($table == 'users') {
                \Illuminate\Support\Facades\DB::statement("DELETE FROM `$table` WHERE role != 'admin'");
            } else if ($table == 'migrations' || $table == 'password_reset_tokens') {
                // skip
            } else {
                \Illuminate\Support\Facades\DB::statement("TRUNCATE TABLE `$table`");
            }
        }

        \Illuminate\Support\Facades\DB::statement("SET FOREIGN_KEY_CHECKS=1;");

        return redirect()->back()->with('success', 'Data pada tabel yang dipilih berhasil dibersihkan (Akun Admin tetap aman)!');
    }
}
