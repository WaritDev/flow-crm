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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Sales_ID
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('stage_id')->constrained('pipeline_stages')->onDelete('restrict');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('value', 15, 2)->default(0);
            $table->string('currency', 3)->default('THB');
            $table->date('expected_close_date')->nullable();
            // Action-Driven Fields
            $table->string('next_action')->nullable();
            $table->date('next_action_date')->nullable();
            $table->text('lost_reason')->nullable(); // For Manager Analysis [2]
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
