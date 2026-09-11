<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escalations', function (Blueprint $table) {

            $table->id();

            $table->foreignId('breakdown_request_id')
                ->constrained('breakdown_requests')
                ->cascadeOnDelete();

            $table->foreignId('forwarded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('trigger_type');

            $table->text('reason')->nullable();

            $table->string('status')
                ->default('Pending Review');

            $table->string('admin_decision')
                ->nullable();

            $table->text('admin_remarks')
                ->nullable();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('forwarded_at')
                ->nullable();

            $table->timestamp('reviewed_at')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escalations');
    }
};