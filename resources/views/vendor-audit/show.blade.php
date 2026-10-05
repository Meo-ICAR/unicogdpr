<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Questionario Adeguatezza Privacy — {{ $audit->externalProcessor?->name }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f3f4f6; color: #111827; margin: 0; padding: 24px 16px; }
        .card { max-width: 720px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        h1 { font-size: 20px; margin-bottom: 4px; }
        p.subtitle { color: #6b7280; margin-top: 0; }
        label { display: block; font-weight: 600; margin-bottom: 6px; margin-top: 20px; }
        textarea, input[type=file] { width: 100%; box-sizing: border-box; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
        textarea { min-height: 160px; resize: vertical; }
        button { margin-top: 24px; background: #059669; color: #fff; border: none; padding: 12px 20px; border-radius: 8px; font-size: 15px; cursor: pointer; }
        button:hover { background: #047857; }
        .errors { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Questionario di Adeguatezza Privacy</h1>
        <p class="subtitle">{{ $audit->title }} — {{ $audit->externalProcessor?->name }}</p>

        @if ($errors->any())
            <div class="errors">
                <ul style="margin:0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('vendor-audit.submit', $audit->token) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <label for="vendor_answers">Descrivere le misure tecniche e organizzative adottate (Art. 32 GDPR)</label>
            <textarea id="vendor_answers" name="vendor_answers" required placeholder="Es. cifratura dei dati, controllo accessi, backup, formazione del personale, certificazioni possedute...">{{ old('vendor_answers') }}</textarea>

            <label for="evidence">Evidenze / Certificazioni (PDF, Word o immagini — opzionale, più file ammessi)</label>
            <input id="evidence" type="file" name="evidence[]" multiple>

            <button type="submit">Invia Questionario</button>
        </form>
    </div>
</body>
</html>
