@extends('documents.layout')

@section('title', 'Accordo sul Trattamento dei Dati (DPA Art. 28 GDPR) - ' . $processor->name)

@section('content')
<div class="doc-title-box">
    <div class="doc-title">Accordo sul Trattamento dei Dati Personali (DPA)</div>
    <div class="doc-subtitle">Nomina a Sub-Responsabile del Trattamento (Art. 28 Regolamento UE 2016/679 - GDPR)</div>
</div>

<p><strong>TRA LE PARTI:</strong></p>
<ol>
    <li>
        <strong>{{ $company->name }}</strong> (di seguito denominata il <em>"Responsabile Principale"</em> / <em>"Committente"</em>);
    </li>
    <li>
        <strong>{{ $processor->name }}</strong>, P.IVA/C.F. <strong>{{ $processor->tax_number ?? 'N/D' }}</strong>, Email di contatto: <strong>{{ $processor->contact_email ?? 'N/D' }}</strong> (di seguito denominata il <em>"Sub-Responsabile"</em> o <em>"Fornitore"</em>).
    </li>
</ol>

<div class="box-highlight">
    <strong>Oggetto dell'Incarico:</strong> {{ $servicesDescription }}<br>
    <strong>Validità Accordo:</strong> Dal {{ $date->format('d/m/Y') }} al {{ $expiresAt->format('d/m/Y') }}
</div>

<p><strong>SI CONVIENE E STIPULA QUANTO SEGUE:</strong></p>

<h2>1. Istruzioni Documentate e Finalità del Trattamento</h2>
<p>
    Il Sub-Responsabile si obbliga a trattare i dati personali unicamente per conto del Committente, nel rispetto delle istruzioni documentate fornite e per le sole finalità connesse all'esecuzione del contratto di servizio principale.
</p>

<h2>2. Categorie di Dati e Interessati</h2>
<p>
    I trattamenti affidati possono riguardare dati anagrafici, numeri telefonici, indirizzi email, dati di navigazione, log tecnici o informazioni contrattuali di clienti, prospect, dipendenti o referenti del Committente.
</p>

<h2>3. Misure Tecniche e Organizzative di Sicurezza (TOMs - Art. 32)</h2>
<p>Il Sub-Responsabile garantisce l'adozione e il mantenimento delle seguenti misure di sicurezza:</p>
<ul>
    <li>Cifratura dei dati in transito (TLS/HTTPS) e a riposo (AES-256);</li>
    <li>Politiche di controllo degli accessi basate sul principio del minimo privilegio;</li>
    <li>Procedure di backup periodico e capacità di ripristino rapido dei dati (Disaster Recovery);</li>
    <li>Verifica periodica e collaudo dell'efficacia delle misure tecniche applicate.</li>
</ul>

<h2>4. Notifica di Data Breach (Art. 33 GDPR)</h2>
<p>
    In caso di incidente di sicurezza che comporti la distruzione, perdita, modifica o accesso non autorizzato ai dati personali trattati per conto del Committente, il Sub-Responsabile ha l'obbligo di notificare l'evento al Committente <strong>entro e non oltre 24/48 ore</strong> dalla presa di conoscenza, fornendo tutti gli elementi utili per la valutazione dell'impatto.
</p>

<h2>5. Sub-Affidamento e Trasferimenti Extra-UE (Art. 44+)</h2>
<p>
    Il Sub-Responsabile non potrà ricorrere ad altri sub-incaricati né trasferire dati al di fuori dello Spazio Economico Europeo senza la preventiva autorizzazione scritta del Committente e l'applicazione di adeguate garanzie (Clausole Contrattuali Tipo SCC o Data Privacy Framework).
</p>

<h2>6. Cancellazione o Restituzione dei Dati</h2>
<p>
    Alla cessazione della fornitura, il Sub-Responsabile dovrà, su scelta del Committente, cancellare o restituire tutti i dati personali e le copie esistenti, rilasciando apposita dichiarazione scritta di avvenuta distruzione.
</p>

<table class="signatures-table">
    <tr>
        <td>
            <strong>Per il Committente:</strong>
            <div class="signature-line">
                {{ $company->name }}<br>
                (Rappresentante Legale)
            </div>
        </td>
        <td>
            <strong>Per il Sub-Responsabile:</strong>
            <div class="signature-line">
                {{ $processor->name }}<br>
                (Timbro e Firma per Accettazione)
            </div>
        </td>
    </tr>
</table>
@endsection
