<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_report_id')->constrained('work_reports')->cascadeOnDelete();
            $table->foreignId('confirmed_by')->constrained('users'); // Ministry User
            $table->enum('is_resolved', ['Yes', 'No']);
            $table->text('feedback')->nullable();
            $table->timestamp('confirmed_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_confirmations');
    }
};
