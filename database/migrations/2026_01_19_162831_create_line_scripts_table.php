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
        Schema::create('line_scripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained('pipeline_stages')->onDelete('cascade'); // PROVIDES Relationship
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade'); // IS_A Relationship
            $table->string('name');
            $table->text('content'); // ข้อความสคริปต์ [4]
            $table->integer('use_count')->default(0); // [2]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('line_scripts');
    }
};
