<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $table = Schema::hasTable('user') ? 'user' : 'users';

        if (Schema::hasTable($table) && !Schema::hasColumn($table, 'role')) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('role', 20)->default('user');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table = Schema::hasTable('user') ? 'user' : 'users';

        if (Schema::hasTable($table) && Schema::hasColumn($table, 'role')) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
