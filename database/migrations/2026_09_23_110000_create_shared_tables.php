<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabelle già presenti nel database (in origine condiviso con altre app):
     * create solo se mancano, ad esempio su database nuovi o di test.
     */
    public function up(): void
    {
        if (! Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id('id');
                $table->char('company_id', 36)->default('45d36df8-369f-40ce-b4fd-b5907c342fe9');
                $table->string('name', 255);
                $table->string('address', 255)->nullable();
                $table->string('street_number', 20)->nullable();
                $table->string('city', 100)->nullable();
                $table->string('zip_code', 10)->nullable();
                $table->string('province', 100)->nullable();
                $table->string('region', 100)->nullable();
                $table->string('branchable_type', 255);
                $table->char('branchable_id', 36);
                $table->boolean('is_main_office')->default(0);
                $table->boolean('is_active')->default(1);
                $table->string('manager_first_name', 100)->nullable();
                $table->string('manager_last_name', 100)->nullable();
                $table->string('manager_tax_code', 16)->nullable();
                $table->date('founded_at')->nullable();
                $table->date('dismissed_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('company_roles')) {
            Schema::create('company_roles', function (Blueprint $table) {
                $table->id('id');
                $table->char('company_id', 36);
                $table->string('name', 255)->nullable();
                $table->string('funzione', 255)->nullable();
                $table->boolean('is_external')->default(0);
                $table->date('dal')->nullable();
                $table->date('al')->nullable();
                $table->string('execution_method', 255)->nullable();
                $table->string('expertName', 255)->nullable();
                $table->integer('n')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('document_types')) {
            Schema::create('document_types', function (Blueprint $table) {
                $table->id('id');
                $table->string('name', 255)->nullable();
                $table->string('description', 255)->nullable();
                $table->string('code', 255)->nullable();
                $table->string('codegroup', 255)->nullable();
                $table->string('slug', 255)->nullable();
                $table->string('regex_pattern', 255)->nullable();
                $table->integer('priority')->default(0);
                $table->string('phase', 255)->nullable();
                $table->boolean('is_person')->default(1);
                $table->boolean('is_company')->default(0);
                $table->boolean('is_employee')->default(0);
                $table->boolean('is_agent')->default(0);
                $table->boolean('is_principal')->default(0);
                $table->boolean('is_client')->default(0);
                $table->boolean('is_practice')->default(0);
                $table->string('trigger_field', 255)->nullable();
                $table->boolean('is_signed')->default(0);
                $table->boolean('is_monitored')->default(0);
                $table->unsignedBigInteger('renewed_by_id')->nullable();
                $table->string('document_url', 255)->nullable();
                $table->integer('training_hours')->nullable();
                $table->string('training_organization')->nullable();
                $table->integer('duration')->nullable();
                $table->string('duration_unit', 255)->default('days');
                $table->string('nature', 255)->nullable()->default('incoming');
                $table->string('doctype')->nullable();
                $table->string('cellposition', 255)->nullable();
                $table->string('emitted_by', 255)->nullable();
                $table->boolean('is_sensible')->default(0);
                $table->boolean('is_template')->default(0);
                $table->boolean('is_stored')->default(0);
                $table->string('regex', 255)->nullable();
                $table->boolean('is_endMonth')->default(0);
                $table->boolean('is_AiAbstract')->default(0);
                $table->boolean('is_AiCheck')->default(0);
                $table->text('AiPattern')->nullable();
                $table->unsignedSmallInteger('min_confidence')->default(70);
                $table->boolean('allow_auto_verification')->default(0);
                $table->json('notify_days_before')->nullable();
                $table->unsignedSmallInteger('retention_years')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->boolean('is_versioned')->nullable()->default(0);
                $table->string('document_typable', 255)->nullable();
                $table->string('trigger_state', 255)->nullable();
                $table->string('trigger_value', 255)->nullable();
                $table->string('exclude_field', 255)->nullable();
                $table->string('exclude_state', 255)->nullable();
                $table->string('exclude_value', 255)->nullable();
                $table->integer('expire_days_before')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->char('id', 36);
                $table->char('company_id', 36)->nullable();
                $table->string('documentable_type', 255);
                $table->char('documentable_id', 36);
                $table->unsignedBigInteger('document_type_id')->nullable();
                $table->string('name', 255)->nullable();
                $table->integer('training_hours')->nullable();
                $table->string('training_organization')->nullable();
                $table->string('docnumber', 255)->nullable();
                $table->string('spatie_collection', 100)->default('default');
                $table->string('document_url', 255)->nullable();
                $table->string('status', 50)->default('uploaded');
                $table->timestamp('last_sent_at')->nullable();
                $table->unsignedInteger('reminders_count')->default(0);
                $table->string('sync_status', 50)->default('local');
                $table->string('source_app', 255)->default('local');
                $table->string('app_id', 255)->nullable();
                $table->string('app_drive_id', 255)->nullable();
                $table->string('app_etag', 255)->nullable();
                $table->longText('extracted_text')->nullable();
                $table->json('metadata')->nullable();
                $table->text('ai_abstract')->nullable();
                $table->unsignedSmallInteger('ai_confidence_score')->nullable();
                $table->boolean('is_monitored')->default(0);
                $table->boolean('is_template')->default(0);
                $table->string('doctype')->nullable();
                $table->string('cellposition', 255)->nullable();
                $table->boolean('is_signed')->default(0);
                $table->boolean('is_unique')->default(0);
                $table->boolean('is_endMonth')->default(0);
                $table->string('emitted_by', 255)->nullable();
                $table->date('emitted_at')->nullable();
                $table->date('expires_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamp('signed_at')->nullable();
                $table->text('description')->nullable();
                $table->text('internal_notes')->nullable();
                $table->text('rejection_note')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->char('renewed_by', 36)->nullable();
                $table->unsignedBigInteger('uploaded_by')->nullable();
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->string('file_hash', 64)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('audits')) {
            Schema::create('audits', function (Blueprint $table) {
                $table->id('id');
                $table->char('company_id', 36)->nullable();
                $table->text('name')->nullable();
                $table->string('auditable_type', 255);
                $table->char('auditable_id', 36);
                $table->string('auditor_name', 255)->nullable();
                $table->unsignedBigInteger('organization_id')->nullable();
                $table->date('scheduled_at')->nullable();
                $table->date('executed_at')->nullable();
                $table->string('status', 255)->default('planned');
                $table->string('protocol_number', 255)->nullable();
                $table->string('drive_folder_id', 255)->nullable();
                $table->string('origin_type', 255)->default('internal');
                $table->string('execution_method', 255)->default('documentale');
                $table->text('scope')->nullable();
                $table->string('outcome', 255)->nullable();
                $table->string('severity')->nullable();
                $table->text('summary')->nullable();
                $table->text('auditor_notes')->nullable();
                $table->text('remediation_plan')->nullable();
                $table->date('followup_date')->nullable();
                $table->date('followup_checked')->nullable();
                $table->text('followup_notes')->nullable();
                $table->json('rilievi_codes')->nullable();
                $table->string('collaboratore', 255)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('audit_findings')) {
            Schema::create('audit_findings', function (Blueprint $table) {
                $table->id('id');
                $table->unsignedBigInteger('audit_id');
                $table->char('company_id', 36);
                $table->string('title', 255);
                $table->text('description');
                $table->string('severity', 255)->default('minor');
                $table->string('status', 255)->default('open');
                $table->boolean('requires_investigation')->default(0);
                $table->text('investigation_notes')->nullable();
                $table->date('investigation_deadline')->nullable();
                $table->boolean('requires_corrective_action')->default(1);
                $table->text('corrective_action_description')->nullable();
                $table->unsignedBigInteger('remediation_id')->nullable();
                $table->date('corrective_action_deadline')->nullable();
                $table->date('resolved_at')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('remediations')) {
            Schema::create('remediations', function (Blueprint $table) {
                $table->id('id');
                $table->string('remediation_type')->nullable();
                $table->string('name', 255);
                $table->string('code', 255)->nullable();
                $table->text('description')->nullable();
                $table->integer('timeframe_hours')->nullable();
                $table->string('timeframe_desc', 255)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('suspicious_activity_reports')) {
            Schema::create('suspicious_activity_reports', function (Blueprint $table) {
                $table->id('id');
                $table->char('company_id', 36);
                $table->char('client_id', 36)->nullable();
                $table->string('reportable_type', 255);
                $table->char('reportable_id', 36);
                $table->timestamp('reported_at')->nullable();
                $table->json('anomalies_codes')->nullable();
                $table->text('description');
                $table->string('status')->default('pending');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        // Nessuna azione: le tabelle contengono dati che non devono essere eliminati dal rollback.
    }
};
