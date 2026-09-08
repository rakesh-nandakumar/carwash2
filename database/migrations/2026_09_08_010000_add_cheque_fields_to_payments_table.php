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
            $table->string('cheque_number')->nullable()->after('reference');
            $table->string('bank_name')->nullable()->after('cheque_number');
            $table->date('cheque_due_date')->nullable()->after('bank_name');
            $table->boolean('payment_received')->default(false)->after('cheque_due_date');
            $table->timestamp('payment_received_at')->nullable()->after('payment_received');
            $table->boolean('is_bounced')->default(false)->after('payment_received_at');
            $table->timestamp('bounced_at')->nullable()->after('is_bounced');
            $table->text('bounce_reason')->nullable()->after('bounced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'cheque_number',
                'bank_name',
                'cheque_due_date',
                'payment_received',
                'payment_received_at',
                'is_bounced',
                'bounced_at',
                'bounce_reason',
            ]);
        });
    }
};
