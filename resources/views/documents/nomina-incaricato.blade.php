@extends('documents.layout')

@section('title', 'Lettera di Designazione a Persona Autorizzata al Trattamento - ' . $employee->full_name)

@section('content')
<div class="doc-title-box">
    <div class="doc-title">Atto di Designazione e Istruzioni Operative</div>
    <div class="doc-subtitle">A Persona Autorizzata al Trattamento dei Dati Personali (Art. 29 Regolamento UE 2016/679 - GDPR)</div>
</div>

<p><strong>Spettabile:</strong></p>
<table class="table-data" style="margin-bottom: 18px;">
    <tr>
        <th style="width: 25%;">Nome e Cognome:</th>
        <td style="width: 75%;"><strong>{{ $employee->full_name }}</strong></td>
    </tr>
    <tr>
        <th>Codice Fiscale:</th>
        <td>{{ $employee->tax_code ?? 'Non specificato' }}</td>
    </tr>
    <tr>
        <th>Mansione / Ruolo:</th>
        <td>{{ $jobTitle }}</td>
    </tr>
    <tr>
        <th>Reparto / Unità:</th>
        <td>{{ $department }}</td>
    </tr>
</table>

<p>
    La scrivente società <strong>{{ $company->name }}</strong>, in qualità di <em>Titolare / Responsabile del Trattamento</em>, in conformità a quanto disposto dall'art. 29 del Regolamento (UE) 2016/679 (GDPR) e dalla normativa nazionale vigente, con la presente
</p>

<div class="box-highlight" style="text-align: center; font-weight: bold; font-size: 11pt;">
    DESIGNA IL/LA DOTT./SIG. {{ strtoupper($employee->full_name) }}<br>
    QUALE PERSONA AUTORIZZATA AL TRATTAMENTO DEI DATI PERSONALI
</div>

<p>
    La presente designazione è conferita nell'ambito delle mansioni lavorative a Lei attribuite ed è efficace per tutta la durata del rapporto di lavoro / collaborazione con la nostra struttura.
</p>

<h2>1. Ambito del Trattamento e Banche Dati Autorizzate</h2>
<p>
    Nello svolgimento delle Sue mansioni quotidiane, Lei è autorizzato/a ad accedere ed elaborare esclusivamente i dati personali (comuni, di contatto, operativi o particolari ove espressamente previsto) strettamente necessari all'esecuzione degli incarichi assegnati, tramite gli applicativi aziendali, il CRM, il centralino telefonico/dialer e i gestionali in uso.
</p>

<h2>2. Istruzioni e Misure di Sicurezza Obbligatorie</h2>
<p>Nello svolgimento di ogni operazione di trattamento, la S.V. è tenuta a osservare scrupolosamente le seguenti disposizioni:</p>
<ol>
    <li><strong>Custodia delle Credenziali:</strong> La password di accesso alle postazioni e ai sistemi informatici è strettamente personale e segreta. È fatto espresso divieto di cederla a terzi o colleghi o di annotarla in luoghi visibili.</li>
    <li><strong>Blocco Schermo e Postazione Pulita (Clean Desk):</strong> Allontanandosi anche temporaneamente dalla postazione di lavoro, è obbligatorio bloccare la sessione del computer (Windows+L / blocco schermo). Nessun documento cartaceo contenente dati personali deve essere lasciato incustodito.</li>
    <li><strong>Divieto di Estrazione ed Esportazione Dati:</strong> È severamente vietato scaricare, esportare, copiare o trasferire liste di contatti, anagrafiche, registrazioni audio o file su supporti di memoria esterni personali (chiavette USB, hard disk) o su caselle email personali/cloud privati non autorizzati.</li>
    <li><strong>Trattamento delle Chiamate e Consensi:</strong> Per il personale dedicato all'attività di contatto telefonico/commerciale, operare nel rispetto delle liste validate, verificare la presenza di eventuali opposizioni (Blacklist / Registro Pubblico delle Opposizioni) e raccogliere i consensi secondo le procedure aziendali.</li>
    <li><strong>Segnalazione Tempestiva di Incidenti (Data Breach):</strong> Qualsiasi evento anomalo, sospetto di violazione informatica, smarrimento di dispositivi, ricezione di email di phishing o richiesta anomala di dati da parte di terzi deve essere immediatamente comunicato al referente Privacy/IT aziendale.</li>
    <li><strong>Segreto Professionale e Riservatezza:</strong> I dati personali di cui viene a conoscenza non possono essere comunicati o diffusi a terzi né usati per scopi estranei all'attività lavorativa, anche successivamente alla cessazione del rapporto di lavoro.</li>
</ol>

@if(!empty($customInstructions))
<h2>3. Istruzioni Specifiche Aggiuntive</h2>
<p>{!! nl2br(e($customInstructions)) !!}</p>
@endif

<h2>3. Formazione Continua e Sanzioni</h2>
<p>
    La S.V. è tenuta a partecipare alle sessioni formative periodiche in materia di protezione dati personali (Art. 32 GDPR). Si rammenta che l'inosservanza delle istruzioni fornite costituisce illecito disciplinare sanzionabile ai sensi del CCNL applicato, ferme restando le responsabilità civili e penali previste dalla legge.
</p>

<table class="signatures-table">
    <tr>
        <td>
            <strong>Per il Titolare / La Società:</strong>
            <div class="signature-line">
                (Firma e Timbro del Legale Rappresentante)
            </div>
        </td>
        <td>
            <strong>Per Ricevuta, Accettazione e Presa Visione:</strong>
            <div class="signature-line">
                Il/La Dipendente ({{ $employee->full_name }})
            </div>
        </td>
    </tr>
</table>
@endsection
