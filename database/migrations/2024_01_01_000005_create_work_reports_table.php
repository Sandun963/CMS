<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('officer_assignment_id')->constrained('officer_assignments')->cascadeOnDelete();
            $table->text('problem_identified')->nullable();
            $table->text('work_performed')->nullable();
            $table->text('parts_used')->nullable();
            $table->enum('completion_status', ['Done', 'Not Done'])->default('Not Done');
            $table->text('remarks')->nullable();
            $table->timestamp('attended_at')->nullable();
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_reports');
    }
};
