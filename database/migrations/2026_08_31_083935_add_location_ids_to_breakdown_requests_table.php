<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('breakdown_requests', function (Blueprint $table) {

            $table->foreignId('floor_id')
                ->nullable()
                ->after('department_id')
                ->constrained('floors')
                ->nullOnDelete();

            $table->foreignId('division_id')
                ->nullable()
                ->after('floor_id')
                ->constrained('divisions')
                ->nullOnDelete();

            $table->foreignId('area_id')
                ->nullable()
                ->after('division_id')
                ->constrained('areas')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('breakdown_requests', function (Blueprint $table) {

            $table->dropForeign(['area_id']);
            $table->dropForeign(['division_id']);
            $table->dropForeign(['floor_id']);

            $table->dropColumn([
                'floor_id',
                'division_id',
                'area_id'
            ]);
        });
    }
};