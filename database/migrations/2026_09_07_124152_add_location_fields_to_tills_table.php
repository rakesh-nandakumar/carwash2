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
        Schema::table('tills', function (Blueprint $table) {
            $table->string('location')->nullable()->after('description');
            $table->string('ip_address')->nullable()->after('location');
            $table->foreignId('branch_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tills', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['location', 'ip_address', 'branch_id']);
        });
    }
};
