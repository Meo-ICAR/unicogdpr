@extends('documents.layout')

@section('title', 'Accordo di Riservatezza e Non Divulgazione (NDA) - ' . $employee->full_name)

@section('content')
<div class="doc-title-box">
    <div class="doc-title">Accordo di Riservatezza e Non Divulgazione (NDA)</div>
    <div class="doc-subtitle">Patto di Segretezza e Tutela del Patrimonio Informativo e delle Banche Dati Aziendali</div>
</div>

<p>Tra le seguenti parti:</p>
<ol>
    <li>
        <strong>{{ $company->name }}</strong>, con sede legale e operativa (di seguito denominata la <em>"Società"</em>);
    </li>
    <li>
        <strong>{{ $employee->full_name }}</strong>, C.F. <strong>{{ $employee->tax_code ?? 'N/D' }}</strong>, in qualità di <em>{{ $jobTitle }}</em> (di seguito denominato/a il <em>"Collaboratore"</em>).
    </li>
</ol>

<div class="box-highlight">
    <strong>PREMESSO CHE:</strong>
    <ul style="margin: 4px 0 0 0; padding-left: 16px;">
        <li>Nello svolgimento dell'attività lavorativa, il Collaboratore avrà accesso a informazioni riservate, elenchi clienti/prospect, banche dati di lead, know-how aziendale e segreti commerciali della Società e dei suoi Committenti.</li>
        <li>La tutela di tali informazioni costituisce elemento essenziale e imprescindibile del rapporto fiduciario tra le Parti.</li>
    </ul>
</div>

<p><strong>SI CONVIENE E STIPULA QUANTO SEGUE:</strong></p>

<h2>Art. 1 - Definizione di Informazioni Riservate</h2>
<p>
    Si considerano "Informazioni Riservate" tutti i dati, documenti, elenchi nominativi, liste lead, dati personali trattati per conto di committenti, strategie commerciali, report analitici, software, script di vendita e processi interni a cui il Collaboratore abbia accesso o di cui venga a conoscenza a qualsiasi titolo.
</p>

<h2>Art. 2 - Obblighi di Segretezza e Non Divulgazione</h2>
<p>Il Collaboratore si impegna formalmente a:</p>
<ul>
    <li>Custodire le Informazioni Riservate con il massimo grado di diligenza professionale;</li>
    <li>Non divulgare, trasmettere, comunicare o rendere accessibili le Informazioni Riservate a terzi estranei all'azienda;</li>
    <li>Non utilizzare le Informazioni Riservate, direttamente o indirettamente, per fini personali, concorrenziali o per conto di terzi;</li>
    <li>Non asportare, fotografare, copiare o estrarre copie fisiche o digitali di archivi, banche dati, tabulati o liste contatti.</li>
</ul>

<h2>Art. 3 - Durata del Vincolo di Riservatezza</h2>
<p>
    Gli obblighi di cui al presente accordo rimarranno pienamente validi ed efficaci sia per l'intera durata del rapporto di lavoro / collaborazione, sia per un periodo successivo di <strong>3 (tre) anni</strong> dalla cessazione dello stesso, indipendentemente dalla causa di estinzione del rapporto.
</p>

<h2>Art. 4 - Restituzione dei Beni e Dati Aziendali</h2>
<p>
    Alla cessazione del rapporto, il Collaboratore è tenuto a restituire immediatamente alla Società tutti i documenti, supporti magnetici, dispositivi, credenziali e copie di file contenenti Informazioni Riservate.
</p>

<h2>Art. 5 - Violazione e Penale Risarcitoria</h2>
<p>
    L'eventuale violazione degli obblighi del presente accordo comporterà l'immediata risoluzione del rapporto per giusta causa e l'obbligo di risarcimento di tutti i danni patrimoniali e d'immagine arrecati alla Società, con riserva di denuncia presso le competenti Autorità Giudiziarie ai sensi degli artt. 622 e 623 c.p. (rivelazione di segreti d'ufficio e commerciali) e della disciplina sanzionatoria del GDPR.
</p>

<table class="signatures-table">
    <tr>
        <td>
            <strong>Per la Società:</strong>
            <div class="signature-line">
                (Firma e Timbro)
            </div>
        </td>
        <td>
            <strong>Il Collaboratore per Accettazione:</strong>
            <div class="signature-line">
                {{ $employee->full_name }}
            </div>
        </td>
    </tr>
</table>
@endsection
