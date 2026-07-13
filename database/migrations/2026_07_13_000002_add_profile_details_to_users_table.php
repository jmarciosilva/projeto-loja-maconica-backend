<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nickname')->nullable()->after('name');
            $table->string('cim', 8)->nullable()->unique()->after('nickname');
            $table->string('cpf', 11)->nullable()->unique()->after('cim');
            $table->string('degree')->nullable()->after('cpf');
            $table->string('whatsapp', 20)->nullable()->after('degree');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nickname', 'cim', 'cpf', 'degree', 'whatsapp']);
        });
    }
};
