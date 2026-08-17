<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // IT Head assigns a breakdown request to an Assign Officer
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('breakdown_requests')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users');       // IT Head
            $table->foreignId('assign_officer_id')->constrained('users'); // Assign Officer
            $table->text('note')->nullable();
            $table->timestamp('assigned_at')->useCurrent();
            $table->enum('status', ['Pending', 'Forwarded'])->default('Pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
