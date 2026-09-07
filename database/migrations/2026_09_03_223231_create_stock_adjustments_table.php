<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('business_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->decimal('before_quantity', 14, 3);

            $table->decimal('new_quantity', 14, 3);

            $table->decimal('difference', 14, 3);

            $table->string('reason');

            $table->text('notes')->nullable();

            $table->foreignId('adjusted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('reversed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reversed_at')->nullable();

            $table->text('reversal_reason')->nullable();

            $table->timestamps();

            $table->index([
                'business_id',
                'branch_id'
            ]);

            $table->index([
                'product_id',
                'branch_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};