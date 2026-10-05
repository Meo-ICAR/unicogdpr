@extends('documents.layout')

@section('title', 'Scheda Pratica Transazionale - ' . $protocolNumber)

@section('content')
<div class="doc-title-box">
    <div class="doc-title">Scheda Pratica Transazionale: {{ $protocolNumber }}</div>
    <div class="doc-subtitle">Registro Transactional Audit &mdash; Reclami e Diritti dell'Interessato (Art. 12-22 GDPR)</div>
</div>

<table class="table-data">
    <tr>
        <th style="width: 30%;">ID Protocollo Unico</th>
        <td style="width: 70%;"><strong>{{ $protocolNumber }}</strong></td>
    </tr>
    <tr>
        <th>Interessato / Reclamante</th>
        <td>{{ $complainantName }}@if($complainantFiscalCode) (C.F. {{ $complainantFiscalCode }})@endif</td>
    </tr>
    <tr>
        <th>Recapiti DNC / Contatto</th>
        <td>{{ $complainantContact }}</td>
    </tr>
    <tr>
        <th>Titolari / Responsabili</th>
        <td>{{ $titolareResponsabile }}</td>
    </tr>
    @if($vendorTerzi)
    <tr>
        <th>Vendor / Publisher Terzi</th>
        <td>{{ $vendorTerzi }}</td>
    </tr>
    @endif
    <tr>
        <th>Oggetto / Codici Fornitura</th>
        <td>{{ $oggetto }}</td>
    </tr>
    <tr>
        <th>Esito Finale Pratica</th>
        <td>{{ $esitoFinale }}</td>
    </tr>
</table>

<h2>Registro Transactional Audit &mdash; Cronologia Completa degli Eventi</h2>
<table class="table-data">
    <tr>
        <th style="width: 4%;">N&deg;</th>
        <th style="width: 9%;">Data / Ora</th>
        <th style="width: 14%;">Fase / Tipo Evento</th>
        <th style="width: 14%;">Canale &amp; Direzione</th>
        <th style="width: 24%;">Oggetto &amp; Dettaglio Evento</th>
        <th style="width: 24%;">Azione Tecnico-Operativa Eseguita</th>
        <th style="width: 11%;">Stato Pratica</th>
    </tr>
    @foreach ($events as $event)
        <tr>
            <td>{{ $event->event_sequence }}</td>
            <td>{{ $event->event_at?->format('d/m/Y H:i') }}</td>
            <td>{{ $event->event_phase }}</td>
            <td>
                @if($event->event_channel_label || $event->event_direction)
                    {{ trim("{$event->event_channel_label} {$event->event_direction}") }}
                    @if($event->event_counterparty)
                        <br>({{ $event->event_counterparty }})
                    @endif
                @endif
            </td>
            <td>{{ $event->description }}</td>
            <td>{{ $event->operational_action }}</td>
            <td><strong>{{ $event->phase_status }}</strong></td>
        </tr>
    @endforeach
</table>

<h2>Riepilogo Misure Tecnico-Organizzative ed Evidenze di Compliance</h2>
<ul>
    @if($last->dnc_blacklist_status)
        <li><strong>Blacklist / Inibizione DNC (Art. 21 GDPR):</strong> {{ $last->dnc_blacklist_status }}@if($last->caller_number) &mdash; numerazione/recapito interessato: {{ $last->caller_number }}@endif.</li>
    @endif
    @if($last->log_freeze_retention && $last->log_freeze_retention !== 'N/A')
        <li><strong>Conservazione Log (Log Freeze):</strong> {{ $last->log_freeze_retention }}.</li>
    @endif
    @if($vendorTerzi)
        <li><strong>Audit di Filiera Commerciale:</strong> fascicolo di verifica aperto verso i seguenti soggetti terzi coinvolti nella catena di fornitura: {{ $vendorTerzi }}.</li>
    @endif
    @if($last->escalated_to)
        <li><strong>Escalation esterna:</strong> pratica portata all'attenzione di: {{ strtoupper($last->escalated_to) }}.</li>
    @endif
</ul>
<p style="font-size: 8.5pt; color: #6b7280;">Le misure sopra riportate sono generate automaticamente dai dati registrati nella cronologia del fascicolo (ultimo evento: N&deg;{{ $last->event_sequence }} del {{ $last->event_at?->format('d/m/Y') }}) e non includono dettagli tecnici forensi (es. hash di log, ID sessione) non tracciati in questo registro.</p>

<table class="signatures-table">
    <tr>
        <td>
            <strong>Il Responsabile della Protezione dei Dati (DPO):</strong>
            <div class="signature-line">
                {{ $last->assigned_to ?? '________________________' }}<br>
                (Data e Firma)
            </div>
        </td>
        <td>
            <strong>Il Titolare del Trattamento:</strong>
            <div class="signature-line">
                {{ $company->name }}<br>
                (Data e Firma)
            </div>
        </td>
    </tr>
</table>
@endsection
