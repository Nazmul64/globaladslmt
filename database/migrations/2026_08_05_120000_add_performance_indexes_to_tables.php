<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for high performance indexes.
     */
    public function up(): void
    {
        $this->addIndexSafely('users', 'mobile', 'users_mobile_perf_idx');
        $this->addIndexSafely('users', 'ref_code', 'users_ref_code_perf_idx');
        $this->addIndexSafely('users', 'referred_by', 'users_referred_by_perf_idx');

        $this->addCompositeIndexSafely('deposites', ['user_id', 'status'], 'deposites_user_status_perf_idx');
        $this->addCompositeIndexSafely('userdepositerequests', ['user_id', 'status'], 'userdep_user_status_perf_idx');
        $this->addCompositeIndexSafely('user_widthraws', ['user_id', 'status'], 'userwith_user_status_perf_idx');
        $this->addCompositeIndexSafely('agentbuysellposts', ['agent_id', 'status'], 'agentpost_agent_status_perf_idx');
    }

    private function addIndexSafely(string $table, string $column, string $indexName): void
    {
        if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
            try {
                Schema::table($table, function (Blueprint $t) use ($column, $indexName) {
                    $t->index($column, $indexName);
                });
            } catch (\Throwable $e) {
                // Index already exists
            }
        }
    }

    private function addCompositeIndexSafely(string $table, array $columns, string $indexName): void
    {
        if (Schema::hasTable($table)) {
            try {
                Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                    $t->index($columns, $indexName);
                });
            } catch (\Throwable $e) {
                // Index already exists
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe down
    }
};
