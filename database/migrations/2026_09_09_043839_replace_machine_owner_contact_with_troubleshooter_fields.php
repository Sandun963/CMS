<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('breakdown_requests', function (Blueprint $table) {

            $table->string('troubleshooter_name')
                ->nullable()
                ->after('machine_owner_name');

            $table->string('troubleshooter_contact', 30)
                ->nullable()
                ->after('troubleshooter_name');

            $table->dropColumn('machine_owner_contact');
        });
    }

    public function down(): void
    {
        Schema::table('breakdown_requests', function (Blueprint $table) {

            $table->string('machine_owner_contact', 30)
                ->nullable();

            $table->dropColumn([
                'troubleshooter_name',
                'troubleshooter_contact',
            ]);
        });
    }
};