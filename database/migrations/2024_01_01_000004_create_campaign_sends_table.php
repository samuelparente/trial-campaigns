<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            
            // Indexing status to speed up aggregation queries (pending/sent/failed counts)
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->index();
            
            $table->text('error_message')->nullable();
            
            // Composite unique index to guarantee idempotency at database level
            $table->unique(['campaign_id', 'contact_id']);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_sends');
    }
};