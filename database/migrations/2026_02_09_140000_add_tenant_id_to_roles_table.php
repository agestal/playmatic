<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('roles', 'tenant_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->foreignId('tenant_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('tenants')
                    ->cascadeOnDelete();
            });
        }

        $this->dropUniqueSilently('roles', 'roles_name_guard_name_unique');

        Schema::table('roles', function (Blueprint $table) {
            $table->unique(['tenant_id', 'name', 'guard_name'], 'roles_tenant_name_guard_unique');
            $table->index(['tenant_id', 'guard_name'], 'roles_tenant_guard_index');
        });
    }

    public function down(): void
    {
        $this->dropUniqueSilently('roles', 'roles_tenant_name_guard_unique');
        $this->dropIndexSilently('roles', 'roles_tenant_guard_index');

        if (Schema::hasColumn('roles', 'tenant_id')) {
            $this->dropForeignSilently('roles', ['tenant_id']);

            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }

        Schema::table('roles', function (Blueprint $table) {
            $table->unique(['name', 'guard_name'], 'roles_name_guard_name_unique');
        });
    }

    private function dropUniqueSilently(string $tableName, string $index): void
    {
        try {
            Schema::table($tableName, function (Blueprint $table) use ($index) {
                $table->dropUnique($index);
            });
        } catch (\Throwable) {
            // index may not exist in some environments
        }
    }

    private function dropIndexSilently(string $tableName, string $index): void
    {
        try {
            Schema::table($tableName, function (Blueprint $table) use ($index) {
                $table->dropIndex($index);
            });
        } catch (\Throwable) {
            // index may not exist in some environments
        }
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function dropForeignSilently(string $tableName, array $columns): void
    {
        try {
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                $table->dropForeign($columns);
            });
        } catch (\Throwable) {
            // foreign key may not exist in some environments
        }
    }
};
