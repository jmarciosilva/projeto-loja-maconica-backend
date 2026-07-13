<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lodges', function (Blueprint $table) {
            $table->string('potencia')->nullable()->after('phone');
            $table->string('rito')->nullable()->after('potencia');
            $table->string('type')->nullable()->after('rito');
            $table->string('address_zip_code', 9)->nullable()->after('type');
            $table->string('address_street')->nullable()->after('address_zip_code');
            $table->string('address_number', 20)->nullable()->after('address_street');
            $table->string('address_complement')->nullable()->after('address_number');
            $table->string('address_neighborhood')->nullable()->after('address_complement');
            $table->string('address_city')->nullable()->after('address_neighborhood');
            $table->string('address_state', 2)->nullable()->after('address_city');
            $table->string('referral_source')->nullable()->after('address_state');
        });
    }

    public function down(): void
    {
        Schema::table('lodges', function (Blueprint $table) {
            $table->dropColumn([
                'potencia',
                'rito',
                'type',
                'address_zip_code',
                'address_street',
                'address_number',
                'address_complement',
                'address_neighborhood',
                'address_city',
                'address_state',
                'referral_source',
            ]);
        });
    }
};
