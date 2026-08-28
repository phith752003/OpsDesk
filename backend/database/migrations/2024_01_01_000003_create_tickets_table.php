<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 32)->unique();
            $table->string('title');
            $table->text('description');
            $table->string('priority')->index(); // low, medium, high, critical
            $table->string('status')->default('open')->index(); // open, assigned, in_progress, pending, resolved, closed
            
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('agent_id')->nullable()->constrained('users');
            $table->foreignId('sla_policy_id')->nullable()->constrained('sla_policies');

            $table->timestamp('response_due_at')->nullable()->index();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('resolution_due_at')->nullable()->index();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->timestamp('closed_at')->nullable();

            $table->boolean('is_sla_breached')->default(false)->index();
            $table->timestamps();

            // Composite indexes for fast queue filtering & workload aggregation
            $table->index(['status', 'priority'], 'tickets_status_priority_idx');
            $table->index(['agent_id', 'status'], 'tickets_agent_status_idx');
            $table->index(['requester_id', 'created_at'], 'tickets_requester_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
