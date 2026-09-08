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
        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('follow_up_required')->default(false)->after('bounce_reason');
            $table->timestamp('follow_up_date')->nullable()->after('follow_up_required');
            $table->text('follow_up_notes')->nullable()->after('follow_up_date');
            $table->boolean('replacement_payment_received')->default(false)->after('follow_up_notes');
            $table->foreignId('replacement_payment_id')->nullable()->constrained('payments')->nullOnDelete()->after('replacement_payment_received');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'follow_up_required',
                'follow_up_date',
                'follow_up_notes',
                'replacement_payment_received',
                'replacement_payment_id',
            ]);
        });
    }
};
