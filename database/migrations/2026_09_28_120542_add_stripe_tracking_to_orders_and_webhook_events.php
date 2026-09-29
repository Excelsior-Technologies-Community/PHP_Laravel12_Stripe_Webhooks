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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('product_name');
            }

            if (!Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 10)->default('usd')->after('amount');
            }

            if (!Schema::hasColumn('orders', 'payment_intent_id')) {
                $table->string('payment_intent_id')->nullable()->after('stripe_session_id');
            }

            if (!Schema::hasColumn('orders', 'failure_reason')) {
                $table->text('failure_reason')->nullable()->after('payment_status');
            }

            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('failure_reason');
            }
        });

        Schema::table('webhook_events', function (Blueprint $table) {
            if (!Schema::hasColumn('webhook_events', 'event_created_at')) {
                $table->timestamp('event_created_at')->nullable()->after('event_type');
            }

            if (!Schema::hasColumn('webhook_events', 'error_message')) {
                $table->text('error_message')->nullable()->after('status');
            }

            if (!Schema::hasColumn('webhook_events', 'attempts')) {
                $table->unsignedInteger('attempts')->default(0)->after('error_message');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = [
                'customer_email',
                'currency',
                'payment_intent_id',
                'failure_reason',
                'paid_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('webhook_events', function (Blueprint $table) {
            $columns = [
                'event_created_at',
                'error_message',
                'attempts',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('webhook_events', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};