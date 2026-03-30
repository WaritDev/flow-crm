<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->morphs('targetable'); 
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['revenue', 'deals', 'activities'])->default('revenue');
            $table->unsignedTinyInteger('month');
            $table->integer('year');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(
                ['targetable_id', 'targetable_type', 'month', 'year', 'type'], 
                'unique_target_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};