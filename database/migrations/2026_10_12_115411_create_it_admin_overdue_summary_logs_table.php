<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('it_admin_overdue_summary_logs', function (Blueprint $table) {
            $table->id();
            $table->date('summary_date');
            $table->string('delivery_mode', 10);
            $table->string('recipient_key', 80);
            $table->string('recipient_email');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['summary_date', 'delivery_mode', 'recipient_key'],
                'it_admin_overdue_summary_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('it_admin_overdue_summary_logs');
    }
};
