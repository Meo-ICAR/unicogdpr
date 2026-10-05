@extends('documents.layout')

@section('title', 'Dossier di Notifica Data Breach (Art. 33/34 GDPR) - ' . $breach->name)

@section('content')
<div class="doc-title-box" style="border-left-color: #dc2626;">
    <div class="doc-title" style="color: #991b1b;">Scheda Notifica / Dossier Data Breach</div>
    <div class="doc-subtitle">Registro Incidenti di Sicurezza (Artt. 33 e 34 Regolamento UE 2016/679 - GDPR)</div>
</div>

<table class="table-data">
    <tr>
        <th style="width: 30%;">Identificativo Incidente:</th>
        <td style="width: 70%;"><strong>{{ $breach->name }}</strong></td>
    </tr>
    <tr>
        <th>Data/Ora di Scoperta:</th>
        <td>{{ $breach->discovered_at ? $breach->discovered_at->format('d/m/Y H:i') : 'N/D' }}</td>
    </tr>
    <tr>
        <th>Data/Ora Presunta Accadimento:</th>
        <td>{{ $breach->occurred_at ? $breach->occurred_at->format('d/m/Y H:i') : 'N/D' }}</td>
    </tr>
    <tr>
        <th>Livello di Gravità / Severità:</th>
        <td>
            <span style="font-weight: bold; text-transform: uppercase; color: {{ in_array($breach->severity, ['high', 'critical']) ? '#dc2626' : '#d97706' }};">
                {{ $breach->severity ?? 'Non specificato' }}
            </span>
        </td>
    </tr>
    <tr>
        <th>Stato Pratica:</th>
        <td>{{ ucfirst($breach->status ?? 'In corso') }}</td>
    </tr>
    <tr>
        <th>Obbligo Notifica Garante (Art. 33):</th>
        <td><strong>{{ $breach->is_notifiable_to_authority ? 'SÌ (Entro 72 ore)' : 'NO (Rischio non rilevante)' }}</strong></td>
    </tr>
    <tr>
        <th>Obbligo Comunicazione agli Interessati (Art. 34):</th>
        <td><strong>{{ $breach->is_notifiable_to_subjects ? 'SÌ (Rischio elevato)' : 'NO' }}</strong></td>
    </tr>
</table>

<h2>1. Descrizione dell'Evento e Natura della Violazione</h2>
<p><strong>Natura della violazione:</strong> {{ $breach->nature_of_breach ?? 'Violazione di riservatezza / integrità / disponibilità' }}</p>
<p>{!! nl2br(e($breach->description ?? 'Nessuna descrizione inserita.')) !!}</p>

<h2>2. Dati e Categorie di Interessati Coinvolti</h2>
<table class="table-data">
    <tr>
        <th style="width: 35%;">Numero Approssimativo Record:</th>
        <td style="width: 65%;"><strong>{{ $breach->approximate_records_count ? number_format($breach->approximate_records_count, 0, ',', '.') : 'In fase di quantificazione' }}</strong></td>
    </tr>
    <tr>
        <th>Tipologie di Dati Coinvolti:</th>
        <td>{{ $breach->affected_data_categories ?? 'Dati anagrafici, di contatto o credenziali' }}</td>
    </tr>
    <tr>
        <th>Categorie di Interessati:</th>
        <td>{{ $breach->affected_individuals ?? 'Clienti, prospect, dipendenti' }}</td>
    </tr>
</table>

<h2>3. Causa Scatenante (Root Cause)</h2>
<p>{!! nl2br(e($breach->root_cause ?? 'Indagine tecnica e forense in corso.')) !!}</p>

<h2>4. Misure di Mitigazione e Azioni Correttive Adottate</h2>
<div class="box-highlight">
    <p><strong>Azioni Immediate (Mitigation):</strong></p>
    <p>{!! nl2br(e($breach->mitigation_actions ?? 'Isolamento dei sistemi impattati e reset credenziali.')) !!}</p>
    <p style="margin-top: 8px;"><strong>Misure Correttive e Preventive (Art. 32):</strong></p>
    <p>{!! nl2br(e($breach->preventive_measures ?? 'Revisione policy di sicurezza, aggiornamento patch e rafforzamento controlli.')) !!}</p>
</div>

<table class="signatures-table">
    <tr>
        <td>
            <strong>Referente Sicurezza / IT Manager:</strong>
            <div class="signature-line">
                (Firma per Attestazione Tecnica)
            </div>
        </td>
        <td>
            <strong>Il DPO / Titolare del Trattamento:</strong>
            <div class="signature-line">
                {{ $company->name }}<br>
                (Data e Firma)
            </div>
        </td>
    </tr>
</table>
@endsection
