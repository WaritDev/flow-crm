<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $orgId = DB::table('organizations')->orderBy('id')->value('id');

        if ($orgId === null) {
            if (DB::table('users')->whereNull('organization_id')->exists()) {
                throw new \RuntimeException(
                    'Cannot rollback make_organization_id_nullable_in_users: no organization exists; create one or set organization_id on all users.'
                );
            }
        } else {
            DB::table('users')
                ->whereNull('organization_id')
                ->update(['organization_id' => $orgId]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable(false)->change();
        });
    }
};
