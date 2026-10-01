<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webhook_events', function (Blueprint $table) {
            if (!Schema::hasColumn('webhook_events', 'is_simulated')) {
                $table->boolean('is_simulated')->default(false)->after('status');
            }

            if (!Schema::hasColumn('webhook_events', 'retry_count')) {
                $table->integer('retry_count')->default(0)->after('is_simulated');
            }

            if (!Schema::hasColumn('webhook_events', 'signature_valid')) {
                $table->boolean('signature_valid')->default(true)->after('retry_count');
            }

            if (!Schema::hasColumn('webhook_events', 'headers_json')) {
                $table->json('headers_json')->nullable()->after('signature_valid');
            }
        });
    }

    public function down(): void
    {
        Schema::table('webhook_events', function (Blueprint $table) {
            $table->dropColumn([
                'is_simulated',
                'retry_count',
                'signature_valid',
                'headers_json',
            ]);
        });
    }
};
