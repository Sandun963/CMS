<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_number_counters', function (Blueprint $table) {

            $table->id();

            // Example: 26, 27, 28
            $table->string('year', 2)->unique();

            // Last request sequence used for that year
            $table->unsignedInteger('last_number')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_number_counters');
    }
};