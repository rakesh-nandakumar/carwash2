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
        Schema::table('audit_logs', function (Blueprint $table) {
            // Drop old columns
            if (Schema::hasColumn('audit_logs', 'user_id')) {
                $table->dropForeign(['user_id']);
            }
            
            $columnsToDrop = [];
            if (Schema::hasColumn('audit_logs', 'user_id')) $columnsToDrop[] = 'user_id';
            if (Schema::hasColumn('audit_logs', 'action')) $columnsToDrop[] = 'action';
            if (Schema::hasColumn('audit_logs', 'entity_type')) $columnsToDrop[] = 'entity_type';
            if (Schema::hasColumn('audit_logs', 'entity_id')) $columnsToDrop[] = 'entity_id';
            if (Schema::hasColumn('audit_logs', 'old_value')) $columnsToDrop[] = 'old_value';
            if (Schema::hasColumn('audit_logs', 'new_value')) $columnsToDrop[] = 'new_value';
            if (Schema::hasColumn('audit_logs', 'reason')) $columnsToDrop[] = 'reason';
            if (Schema::hasColumn('audit_logs', 'ip')) $columnsToDrop[] = 'ip';
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
            
            // Add new columns if they don't exist
            if (!Schema::hasColumn('audit_logs', 'event_key')) {
                $table->string('event_key')->after('id');
            }
            if (!Schema::hasColumn('audit_logs', 'severity')) {
                $table->string('severity')->default('info')->after('event_key');
            }
            if (!Schema::hasColumn('audit_logs', 'description')) {
                $table->text('description')->nullable()->after('severity');
            }
            if (!Schema::hasColumn('audit_logs', 'actor_email')) {
                $table->string('actor_email')->nullable()->after('description');
            }
            if (!Schema::hasColumn('audit_logs', 'actor_type')) {
                $table->string('actor_type')->nullable()->after('actor_email');
            }
            if (Schema::hasColumn('audit_logs', 'ip') && !Schema::hasColumn('audit_logs', 'ip_address')) {
                $table->renameColumn('ip', 'ip_address');
            } elseif (!Schema::hasColumn('audit_logs', 'ip_address')) {
                $table->string('ip_address')->nullable()->after('actor_type');
            }
            if (!Schema::hasColumn('audit_logs', 'is_flagged')) {
                $table->boolean('is_flagged')->default(false)->after('ip_address');
            }
            if (!Schema::hasColumn('audit_logs', 'meta')) {
                $table->json('meta')->nullable()->after('is_flagged');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Drop new columns if they exist
            $columnsToDrop = [];
            if (Schema::hasColumn('audit_logs', 'event_key')) $columnsToDrop[] = 'event_key';
            if (Schema::hasColumn('audit_logs', 'severity')) $columnsToDrop[] = 'severity';
            if (Schema::hasColumn('audit_logs', 'description')) $columnsToDrop[] = 'description';
            if (Schema::hasColumn('audit_logs', 'actor_email')) $columnsToDrop[] = 'actor_email';
            if (Schema::hasColumn('audit_logs', 'actor_type')) $columnsToDrop[] = 'actor_type';
            if (Schema::hasColumn('audit_logs', 'is_flagged')) $columnsToDrop[] = 'is_flagged';
            if (Schema::hasColumn('audit_logs', 'meta')) $columnsToDrop[] = 'meta';
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
            
            // Restore old columns if they don't exist
            if (!Schema::hasColumn('audit_logs', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('audit_logs', 'action')) {
                $table->string('action');
            }
            if (!Schema::hasColumn('audit_logs', 'entity_type')) {
                $table->string('entity_type')->nullable();
            }
            if (!Schema::hasColumn('audit_logs', 'entity_id')) {
                $table->unsignedBigInteger('entity_id')->nullable();
            }
            if (!Schema::hasColumn('audit_logs', 'old_value')) {
                $table->json('old_value')->nullable();
            }
            if (!Schema::hasColumn('audit_logs', 'new_value')) {
                $table->json('new_value')->nullable();
            }
            if (!Schema::hasColumn('audit_logs', 'reason')) {
                $table->text('reason')->nullable();
            }
            if (Schema::hasColumn('audit_logs', 'ip_address') && !Schema::hasColumn('audit_logs', 'ip')) {
                $table->renameColumn('ip_address', 'ip');
            } elseif (!Schema::hasColumn('audit_logs', 'ip')) {
                $table->ipAddress('ip')->nullable();
            }
        });
    }
};
