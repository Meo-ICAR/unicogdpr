<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_subject_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id')->index();
            $table->nullableUuidMorphs('registrable');
            $table->string('requester_name');
            $table->string('requester_email')->nullable();
            $table->string('requester_phone')->nullable();
            $table->enum('request_type', ['access', 'rectification', 'erasure', 'restriction', 'portability', 'objection', 'withdraw_consent', 'other']);
            $table->enum('status', ['received', 'in_progress', 'completed', 'rejected', 'extended'])->default('received');
            $table->date('received_at');
            $table->date('deadline_at');
            $table->date('extended_until')->nullable();
            $table->date('completed_at')->nullable();
            $table->text('request_description');
            $table->text('response_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('identity_verified')->default(false);
            $table->string('identity_verification_method')->nullable();
            $table->enum('channel', ['email', 'pec', 'letter', 'in_person', 'online_form', 'other'])->default('email');
            $table->timestamps();
            $table->softDeletes();

            // FK (Da scommentare se le tabelle esistono)
            // $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            // $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_subject_requests');
    }
};
