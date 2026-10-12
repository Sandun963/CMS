
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overdue_email_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('officer_assignment_id')
                ->constrained('officer_assignments')
                ->cascadeOnDelete();

            $table->string('delivery_mode', 10);
            $table->string('recipient_email');
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['officer_assignment_id', 'delivery_mode'],
                'overdue_email_assignment_mode_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overdue_email_logs');
    }
};
