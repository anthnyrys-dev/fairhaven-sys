<?php

use App\Enums\JobStatus;
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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('job_no')->unique();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->date('date_booked')->nullable();
            $table->string('site_address')->nullable();
            $table->string('suburb')->nullable();
            $table->string('job_type')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('lead_technician_id')->nullable()->constrained('technicians')->nullOnDelete();
            $table->string('status')->default(JobStatus::OPEN->value);
            $table->date('date_completed')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
