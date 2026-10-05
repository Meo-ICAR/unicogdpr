@extends('documents.layout')

@section('title', 'Modulo di Segnalazione Incident / Data Breach - ' . $breach->name)

@section('content')
<div class="doc-title-box" style="border-left-color: #dc2626;">
    <div class="doc-title" style="color: #991b1b;">Modulo di Segnalazione Incident / Data Breach</div>
    <div class="doc-subtitle">Da compilare a cura di chi scopre l'evento o dal referente aziendale ed inviare al Titolare/DPO</div>
</div>

<h2>1. Dati del Segnalante</h2>
<table class="table-data">
    <tr>
        <th style="width: 35%;">Nome e Cognome:</th>
        <td style="width: 65%;">{{ $breach->reporter_name ?: '&nbsp;' }}</td>
    </tr>
    <tr>
        <th>Ruolo/Azienda:</th>
        <td>{{ $breach->reporter_role ?: '&nbsp;' }}</td>
    </tr>
    <tr>
        <th>Recapito Telefonico / E-mail:</th>
        <td>{{ $breach->reporter_contact ?: '&nbsp;' }}</td>
    </tr>
</table>

<h2>2. Dettagli dell'Incidente</h2>
<table class="table-data">
    <tr>
        <th style="width: 35%;">Data e Ora in cui si è verificato l'evento:</th>
        <td style="width: 65%;">{{ $breach->occurred_at ? $breach->occurred_at->format('d/m/Y H:i') : '&nbsp;' }}</td>
    </tr>
    <tr>
        <th>Data e Ora della scoperta dell'evento:</th>
        <td>{{ $breach->discovered_at ? $breach->discovered_at->format('d/m/Y H:i') : '&nbsp;' }}</td>
    </tr>
    <tr>
        <th>Luogo o Sistema coinvolto:</th>
        <td>{{ $breach->affected_system ?: '&nbsp;' }}</td>
    </tr>
</table>

@php
    $natureOptions = [
        'confidentiality' => 'Riservatezza (Divulgazione non autorizzata / Furto)',
        'integrity' => 'Integrità (Alterazione / Manomissione dati)',
        'availability' => 'Disponibilità (Perdita / Cifratura da Ransomware / Distruzione)',
    ];
@endphp

<h2>3. Natura della Violazione</h2>
<table class="table-data">
    @foreach ($natureOptions as $value => $label)
        <tr>
            <td style="width: 30px; text-align: center;">{{ $breach->nature_of_breach === $value || $breach->nature_of_breach === 'combined' ? '☑' : '☐' }}</td>
            <td>{{ $label }}</td>
        </tr>
    @endforeach
</table>

@php
    $dataCategories = collect(explode(',', (string) $breach->affected_data_categories))
        ->map(fn ($c) => trim($c))
        ->filter()
        ->all();
    $categoryOptions = ['Anagrafici', 'Contatti', 'Documenti', 'IBAN'];
    $mandateOptions = ['ECOM', 'Palk', 'Altro'];
@endphp

<h2>4. Categoria e Quantità di Dati e Interessati Coinvolti</h2>
<table class="table-data">
    <tr>
        <th style="width: 35%;">Categorie di Dati:</th>
        <td style="width: 65%;">
            @foreach ($categoryOptions as $option)
                {{ in_array($option, $dataCategories) ? '☑' : '☐' }} {{ $option }}&nbsp;&nbsp;
            @endforeach
        </td>
    </tr>
    <tr>
        <th>Mandataria coinvolta:</th>
        <td>
            @foreach ($mandateOptions as $option)
                {{ $breach->involved_mandate === $option ? '☑' : '☐' }} {{ $option === 'Palk' ? 'Palk (Dati propri)' : $option }}&nbsp;&nbsp;
            @endforeach
        </td>
    </tr>
    <tr>
        <th>Stima del numero di persone coinvolte:</th>
        <td>{{ $breach->approximate_records_count ? number_format($breach->approximate_records_count, 0, ',', '.') : '&nbsp;' }}</td>
    </tr>
</table>

<h2>5. Descrizione Dinamica e Azioni Correttive Immediate Adottate</h2>
<div class="box-highlight" style="min-height: 90px;">
    {!! nl2br(e($breach->description ?: '')) !!}
    @if ($breach->corrective_actions)
        <br><br><strong>Azioni correttive immediate:</strong><br>
        {!! nl2br(e($breach->corrective_actions)) !!}
    @endif
</div>

<table class="signatures-table">
    <tr>
        <td>
            <div class="signature-line">Firma del Segnalante</div>
        </td>
        <td>
            <div class="signature-line">Ricevuto da (Titolare/DPO)</div>
        </td>
    </tr>
</table>
@endsection
