<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('breakdown_requests', function (Blueprint $table) {
            $table->string('status')
                ->default('New')
                ->change();
        });

        Schema::table('officer_assignments', function (Blueprint $table) {
            $table->string('status')
                ->default('Pending')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('breakdown_requests', function (Blueprint $table) {
            $table->enum('status', [
                'New',
                'Assigned',
                'In Progress',
                'Resolved',
                'Closed',
                'Reopened'
            ])->default('New')->change();
        });

        Schema::table('officer_assignments', function (Blueprint $table) {
            $table->enum('status', [
                'Pending',
                'In Progress',
                'Done'
            ])->default('Pending')->change();
        });
    }
};