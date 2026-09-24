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
        Schema::table('till_closures', function (Blueprint $table) {
            $table->string('opening_variance_reason', 500)->nullable()->after('variance_reason');
            $table->decimal('opening_variance', 10, 2)->default(0)->after('opening_variance_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('till_closures', function (Blueprint $table) {
            $table->dropColumn(['opening_variance_reason', 'opening_variance']);
        });
    }
};
