<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breakdown_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); // BRK-2026-0001
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('location')->nullable(); // room / floor detail
            $table->enum('priority', ['Low', 'Medium', 'High'])->default('Medium');
            $table->enum('status', ['New', 'Assigned', 'In Progress', 'Resolved', 'Closed', 'Reopened'])->default('New');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // denormalized current handler (technical officer), kept in sync via observer
            $table->timestamp('received_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breakdown_requests');
    }
};
