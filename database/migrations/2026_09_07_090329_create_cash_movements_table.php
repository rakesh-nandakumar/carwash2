<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
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

            /*
             * Direction of money.
             *
             * in  = money enters Till
             * out = money leaves Till
             */
            $table->string('type');

            /*
             * Why the movement happened.
             *
             * sale
             * refund
             * manual
             * drop
             */
            $table->string('source');

            $table->decimal('amount', 14, 2);

            /*
             * Optional polymorphic business reference.
             *
             * Payment
             * Refund
             * Invoice
             * etc.
             */
            $table->nullableMorphs('reference');

            $table->string('reason')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();

            $table->index([
                'tenant_id',
                'till_id',
                'created_at',
            ]);

            $table->index([
                'tenant_id',
                'type',
                'source',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};