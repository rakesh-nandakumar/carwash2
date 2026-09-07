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
        Schema::create('till_closures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('till_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->decimal('opening_balance', 14, 2)
                ->default(0);

            $table->decimal('expected_balance', 14, 2)
                ->default(0);

            $table->decimal('counted_balance', 14, 2)
                ->default(0);

            $table->decimal('discrepancy', 14, 2)
                ->default(0);

            $table->decimal('cash_sales', 14, 2)
                ->default(0);

            $table->decimal('cash_in', 14, 2)
                ->default(0);

            $table->decimal('cash_out', 14, 2)
                ->default(0);

            $table->decimal('cash_refunds', 14, 2)
                ->default(0);

            $table->decimal('cash_drops', 14, 2)
                ->default(0);

            $table->json('denomination_breakdown')
                ->nullable();

            $table->text('notes')
                ->nullable();

            $table->timestamp('opened_at')
                ->nullable();

            $table->timestamp('closed_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'tenant_id',
                'till_id',
                'closed_at',
            ]);

            $table->index([
                'tenant_id',
                'user_id',
                'closed_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('till_closures');
    }
};
