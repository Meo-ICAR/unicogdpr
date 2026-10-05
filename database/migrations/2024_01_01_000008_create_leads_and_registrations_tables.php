<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_return_logs', function (Blueprint $table) {
            $table->comment('Registro dei lead scartati o resi dai partner (es. errati o opt-out)');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->nullableMorphs('clientable');
            $table->enum('status', ['bounce', 'opt_out_requested', 'converted'])->default('bounce')->comment('Motivazione del reso');
            $table->timestamp('reported_at')->nullable()->comment('Data e ora della segnalazione');
            $table->timestamps();
        });

        Schema::create('lead_transfers', function (Blueprint $table) {
            $table->comment('Registro della cessione e trasferimento sicuro dei lead a terzi');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->morphs('leadable');
            $table->morphs('purchaserable');
            $table->timestamp('transferred_at')->nullable()->comment('Data e ora esecuzione del trasferimento');
            $table->decimal('price', 10, 2)->nullable()->comment('Prezzo o corrispettivo di cessione');
            $table->enum('transfer_method', ['api_tls', 'sftp', 'encrypted_csv'])->default('api_tls')->comment('Canale di trasmissione protetta adottato');
            $table->timestamps();
        });

        Schema::create('registrations', function (Blueprint $table) {
            $table->comment('Registro generale eventi, autorizzazioni e variazioni sulle entità aziendali');
            $table->id();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete()->comment('Riferimento all\'azienda tenant');
            $table->string('name')->comment('Nome o titolo della registrazione');
            $table->nullableMorphs('registrable');
            $table->string('value')->nullable()->comment('Valore registrato');
            $table->string('code')->nullable()->comment('Codice o numero protocollo');
            $table->string('code_internal')->nullable()->comment('Codice identificativo interno');
            $table->text('description')->nullable()->comment('Descrizione dell\'evento o autorizzazione');
            $table->timestamp('start_at')->nullable()->comment('Data di decorrenza ed efficacia');
            $table->timestamp('end_at')->nullable()->comment('Data di scadenza o termine');
            $table->text('reason')->nullable()->comment('Motivazione formale o note aggiuntive');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('lead_transfers');
        Schema::dropIfExists('lead_return_logs');
    }
};
