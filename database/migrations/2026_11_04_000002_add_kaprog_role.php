<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up(): void { DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','admin','guru','siswa','orangtua','kaprog') NOT NULL DEFAULT 'siswa'"); }
 public function down(): void { DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('superadmin','admin','guru','siswa','orangtua') NOT NULL DEFAULT 'siswa'"); }
};
