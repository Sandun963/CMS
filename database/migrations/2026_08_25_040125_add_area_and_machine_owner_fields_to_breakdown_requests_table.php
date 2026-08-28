<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('breakdown_requests', function (Blueprint $table) {

            $table->string('area')->nullable();

            $table->string('machine_owner_name')->nullable();

            $table->string('machine_owner_contact', 30)->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('breakdown_requests', function (Blueprint $table) {

            $table->dropColumn([
                'area',
                'machine_owner_name',
                'machine_owner_contact'
            ]);

        });
    }
};