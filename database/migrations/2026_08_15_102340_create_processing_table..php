<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot tables dropped first (FK dependency order)
        Schema::dropIfExists('processing_activity_privacy_security');
        Schema::dropIfExists('processing_activity_privacy_data_type');
        Schema::dropIfExists('processing_activities');

        Schema::create('processing_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('company_id')
                ->constrained('companies')
                ->cascadeOnDelete()
                ->comment('Riferimento all\'azienda tenant');
            $table->string('code')->nullable();
            $table->string('name');
            $table->string('role')->default('controller');
            $table->foreignId('client_controller_id')
                ->nullable()
                ->constrained('client_controllers')
                ->nullOnDelete();
            $table->text('purposes')->nullable();
            $table->text('legal_basis')->nullable();
            $table->text('data_subject_categories')->nullable();
            $table->text('recipients')->nullable();
            $table->boolean('has_third_country_transfers')->default(false);
            $table->text('third_countries_details')->nullable();
            $table->text('retention_policy')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('processing_activity_privacy_data_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processing_activity_id')
                ->constrained()
                ->cascadeOnDelete()
                ->index('pa_pdt_activity_id_foreign');
            $table->foreignId('privacy_data_type_id')
                ->constrained()
                ->cascadeOnDelete()
                ->index('pa_pdt_data_type_id_foreign');
        });

        Schema::create('processing_activity_privacy_security', function (Blueprint $table) {
            $table->id();
            $table->foreignId('processing_activity_id')
                ->constrained()
                ->cascadeOnDelete()
                ->index('pa_ps_activity_id_foreign');
            $table->foreignId('privacy_security_id')
                ->constrained()
                ->cascadeOnDelete()
                ->index('pa_ps_security_id_foreign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processing_activity_privacy_security');
        Schema::dropIfExists('processing_activity_privacy_data_type');
        Schema::dropIfExists('processing_activities');
    }
};
