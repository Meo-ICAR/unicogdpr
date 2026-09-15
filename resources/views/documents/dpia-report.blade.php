@extends('documents.layout')

@section('title', 'Valutazione d\'Impatto sulla Protezione dei Dati (DPIA) - ' . $dpia->name)

@section('content')
<div class="doc-title-box">
    <div class="doc-title">Valutazione d'Impatto sulla Protezione dei Dati (DPIA)</div>
    <div class="doc-subtitle">Data Protection Impact Assessment (Art. 35 Regolamento UE 2016/679 - GDPR)</div>
</div>

<table class="table-data">
    <tr>
        <th style="width: 30%;">Titolare del Trattamento:</th>
        <td style="width: 70%;"><strong>{{ $company->name }}</strong></td>
    </tr>
    <tr>
        <th>Titolo della Valutazione:</th>
        <td>{{ $dpia->name }}</td>
    </tr>
    <tr>
        <th>Trattamento di Riferimento (Art. 30):</th>
        <td>{{ $dpia->processingActivity?->name ?? 'Non collegato' }}</td>
    </tr>
    <tr>
        <th>Stato:</th>
        <td><strong>{{ $dpia->status === 'completed' ? 'COMPLETATA E VALIDATA DAL DPO' : ucfirst($dpia->status) }}</strong></td>
    </tr>
</table>

<h2>1. Descrizione Sistematica del Trattamento</h2>
<p>{!! nl2br(e($dpia->description_of_processing ?? 'Non specificata.')) !!}</p>

<h2>2. Valutazione di Necessità e Proporzionalità</h2>
<p>{!! nl2br(e($dpia->necessity_assessment ?? 'Non specificata.')) !!}</p>
<table class="table-data">
    <tr>
        <th style="width: 50%;">Trattamento ritenuto necessario:</th>
        <td style="width: 50%;"><strong>{{ $dpia->is_necessary ? 'SÌ' : 'NO' }}</strong></td>
    </tr>
    <tr>
        <th>Trattamento ritenuto proporzionato:</th>
        <td><strong>{{ $dpia->is_proportional ? 'SÌ' : 'NO' }}</strong></td>
    </tr>
</table>

<h2>3. Analisi dei Rischi per i Diritti e le Libertà degli Interessati</h2>
<table class="table-data">
    <tr>
        <th>Fonte del Rischio</th>
        <th>Impatto Potenziale</th>
        <th>Probabilità</th>
        <th>Gravità</th>
        <th>Rischio Intrinseco</th>
        <th>Rischio Residuo</th>
    </tr>
    @forelse ($dpia->items as $item)
        <tr>
            <td>{{ $item->riskCatalog?->name ? $item->riskCatalog->name . ' — ' : '' }}{{ $item->risk_source }}</td>
            <td>{{ $item->impactCatalog?->name ? $item->impactCatalog->name . ' — ' : '' }}{{ $item->potential_impact }}</td>
            <td>{{ $item->probability }}</td>
            <td>{{ $item->severity }}</td>
            <td>{{ $item->inherent_risk_score }}</td>
            <td>{{ $item->residual_risk_score }}</td>
        </tr>
    @empty
        <tr><td colspan="6">Nessun rischio analizzato.</td></tr>
    @endforelse
</table>

<h2>4. Parere del Responsabile della Protezione dei Dati (DPO)</h2>
<div class="box-highlight">
    <p>{!! nl2br(e($dpia->dpo_opinion ?? 'Parere non ancora espresso.')) !!}</p>
</div>

@if ($dpia->isSignedByDpo())
<h2>5. Validazione Formale e Log di Accountability (Art. 5.2 GDPR)</h2>
<table class="table-data">
    <tr>
        <th style="width: 30%;">Validata da:</th>
        <td style="width: 70%;">{{ $dpia->dpoSignedBy?->name }}</td>
    </tr>
    <tr>
        <th>Data e ora validazione:</th>
        <td>{{ $dpia->dpo_signed_at->format('d/m/Y H:i') }}</td>
    </tr>
    <tr>
        <th>Impronta SHA-256 del contenuto:</th>
        <td style="font-family: monospace; font-size: 9px;">{{ $dpia->dpo_signature_hash }}</td>
    </tr>
</table>
<p style="font-size: 10px; color: #555;">L'impronta sopra riportata è calcolata sul contenuto sostanziale della DPIA al momento della validazione: qualunque modifica successiva ai dati sorgente produrrebbe un'impronta differente, rendendo rilevabile un'alterazione non autorizzata.</p>
@endif

<table class="signatures-table">
    <tr>
        <td>
            <strong>Il Titolare del Trattamento:</strong>
            <div class="signature-line">
                {{ $company->name }}<br>
                (Data e Firma)
            </div>
        </td>
        <td>
            <strong>Il Responsabile della Protezione dei Dati (DPO):</strong>
            <div class="signature-line">
                {{ $dpia->dpoSignedBy?->name ?? '________________________' }}<br>
                (Data e Firma)
            </div>
        </td>
    </tr>
</table>
@endsection
