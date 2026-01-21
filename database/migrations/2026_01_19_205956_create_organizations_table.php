<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });


        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
        });


        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')->after('id')->constrained()->onDelete('cascade');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('organization_id')->after('id')->constrained()->onDelete('cascade');
        });

        Schema::table('deals', function (Blueprint $table) {
            $table->foreignId('organization_id')->after('id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
            });
        } catch (Illuminate\Database\QueryException $e) {
            // Foreign key might not exist
        }

        if (Schema::hasColumn('customers', 'organization_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('organization_id');
            });
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
            });
        } catch (Illuminate\Database\QueryException $e) {
            // Foreign key might not exist
        }

        if (Schema::hasColumn('users', 'organization_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('organization_id');
            });
        }

        try {
            Schema::table('teams', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
            });
        } catch (Illuminate\Database\QueryException $e) {
            // Foreign key might not exist
        }

        if (Schema::hasColumn('teams', 'organization_id')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->dropColumn('organization_id');
            });
        }

        try {
            Schema::table('deals', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
            });
        } catch (Illuminate\Database\QueryException $e) {
            // Foreign key might not exist
        }

        if (Schema::hasColumn('deals', 'organization_id')) {
            Schema::table('deals', function (Blueprint $table) {
                $table->dropColumn('organization_id');
            });
        }

        // 4. ลบตารางแม่ออกเป็นลำดับสุดท้าย
        Schema::dropIfExists('organizations');
    }
};
