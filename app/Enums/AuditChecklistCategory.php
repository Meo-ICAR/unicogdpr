<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AuditChecklistCategory: string implements HasColor, HasLabel
{
    case DocumentazioneCompliance = 'documentazione_compliance';
    case ProcedureTeleselling = 'procedure_teleselling';
    case LeadGenerationConsensi = 'lead_generation_consensi';
    case SistemiSicurezza = 'sistemi_sicurezza';
    case PersonaleFormazione = 'personale_formazione';
    case DocumentazioneAggiuntiva = 'documentazione_aggiuntiva';

    public function getLabel(): string
    {
        return $this->label();
    }

    public function getColor(): string
    {
        return $this->color();
    }

    public function label(): string
    {
        return match ($this) {
            self::DocumentazioneCompliance => 'Documentazione Autorizzativa e di Compliance',
            self::ProcedureTeleselling => 'Procedure Operative Teleselling',
            self::LeadGenerationConsensi => 'Lead Generation, Consensi e Liste Contatti',
            self::SistemiSicurezza => 'Sistemi, Sicurezza e Software',
            self::PersonaleFormazione => 'Personale e Formazione',
            self::DocumentazioneAggiuntiva => 'Documentazione Aggiuntiva Consigliata',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DocumentazioneCompliance => 'danger',
            self::ProcedureTeleselling => 'warning',
            self::LeadGenerationConsensi => 'info',
            self::SistemiSicurezza => 'primary',
            self::PersonaleFormazione => 'gray',
            self::DocumentazioneAggiuntiva => 'success',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->label()])
            ->all();
    }
}
