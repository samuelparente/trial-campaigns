<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_contact_list', function (Blueprint $table) {
            $table->id();
            // Foreign keys with cascade delete to maintain referential integrity
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_list_id')->constrained()->onDelete('cascade');
            
            // Prevent duplicate entries of the same contact in the same list
            $table->unique(['contact_id', 'contact_list_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_contact_list');
    }
};