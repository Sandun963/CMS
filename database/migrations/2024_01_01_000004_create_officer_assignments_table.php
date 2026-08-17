<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Assign Officer forwards the request to a specific Technical Officer
        Schema::create('officer_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('technical_officer_id')->constrained('users');
            $table->foreignId('assigned_by')->constrained('users'); // Assign Officer
            $table->date('due_date')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->enum('status', ['Pending', 'In Progress', 'Done'])->default('Pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('officer_assignments');
    }
};
