<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('choices', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('question_id');
            $table->text('text'); 
            $table->boolean('is_correct');
            $table->timestamps();

            // Define foreign key constraint
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
      
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('choices');
    }
};
