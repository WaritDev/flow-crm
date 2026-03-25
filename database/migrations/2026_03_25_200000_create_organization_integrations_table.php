<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();

            // Service user that owns API tokens for integrations (n8n)
            $table->foreignId('n8n_service_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('n8n_token_name')->nullable(); // label only; token value is not stored

            // For LINE OA -> n8n webhook routing
            $table->string('line_webhook_secret', 100);
            $table->string('line_webhook_path', 191);

            // Optional: only needed if FlowCRM itself will call LINE Messaging API
            $table->text('line_channel_access_token_encrypted')->nullable();

            $table->timestamps();

            $table->unique('organization_id');
            $table->unique('line_webhook_path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_integrations');
    }
};

