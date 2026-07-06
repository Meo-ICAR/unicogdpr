<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('company_id')->index();
            $table->nullableUuidMorphs('ownerable'); // chi ha subito la formazione (dipendente, collaboratore, ecc.)
            $table->string('course_name');
            $table->text('course_description')->nullable();
            $table->string('provider')->nullable();
            $table->string('trainer')->nullable();
            $table->enum('delivery_mode', ['in_person', 'online', 'blended', 'on_the_job', 'webinar'])->default('in_person');
            $table->date('training_date');
            $table->date('expiry_date')->nullable();
            $table->decimal('hours', 5, 1)->default(0.0);
            $table->enum('outcome', ['passed', 'failed', 'attended', 'partial'])->default('attended');
            $table->decimal('score', 5, 2)->nullable();
            $table->boolean('certificate_issued')->default(false);
            $table->string('certificate_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // FK (Da scommentare se le tabelle companies ed employees/users esistono)
            // $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            // $table->foreign('employee_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_records');
    }
};
