<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Documento GDPR')</title>
    <style>
        @page {
            margin: 25mm 20mm 25mm 20mm;
            @bottom-right {
                content: "Pagina " counter(page) " di " counter(pages);
            }
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10.5pt;
            line-height: 1.5;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 12px;
            margin-bottom: 25px;
        }
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #1e3a8a;
            letter-spacing: 0.5px;
        }
        .company-subtitle {
            font-size: 8.5pt;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-meta {
            text-align: right;
            font-size: 8.5pt;
            color: #4b5563;
        }
        .doc-title-box {
            background-color: #f3f4f6;
            border-left: 4px solid #2563eb;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        .doc-title {
            font-size: 13pt;
            font-weight: bold;
            color: #111827;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }
        .doc-subtitle {
            font-size: 9.5pt;
            color: #4b5563;
            margin: 0;
        }
        h2 {
            font-size: 11pt;
            font-weight: bold;
            color: #1e3a8a;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
            margin-top: 18px;
            margin-bottom: 8px;
        }
        p {
            margin: 0 0 10px 0;
            text-align: justify;
        }
        ul, ol {
            margin: 0 0 12px 0;
            padding-left: 20px;
        }
        li {
            margin-bottom: 5px;
            text-align: justify;
        }
        .box-highlight {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 4px;
            padding: 10px 14px;
            margin: 12px 0;
            font-size: 9.5pt;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            font-size: 9.5pt;
        }
        .table-data th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: bold;
            text-align: left;
            padding: 6px 10px;
            border: 1px solid #d1d5db;
        }
        .table-data td {
            padding: 6px 10px;
            border: 1px solid #e5e7eb;
        }
        .signatures-table {
            width: 100%;
            margin-top: 35px;
            page-break-inside: avoid;
        }
        .signatures-table td {
            width: 50%;
            vertical-align: top;
            padding: 0 15px;
        }
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #374151;
            padding-top: 6px;
            font-size: 9pt;
            color: #374151;
            text-align: center;
        }
        .footer-note {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            font-size: 7.5pt;
            color: #9ca3af;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 4px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="company-name">{{ $company->name ?? 'Azienda' }}</div>
                <div class="company-subtitle">Sistema di Gestione Conformità GDPR (Reg. UE 2016/679)</div>
            </td>
            <td class="doc-meta" style="vertical-align: middle;">
                <strong>Data emissione:</strong> {{ $date->format('d/m/Y') }}<br>
                <strong>Rif. Interno:</strong> GDPR-DOC-{{ $date->format('Y') }}-{{ substr(md5($employee->id ?? $processor->id ?? $breach->id ?? rand()), 0, 6) }}
            </td>
        </tr>
    </table>

    @yield('content')

    <div class="footer-note">
        Documento conforme al Regolamento Generale sulla Protezione dei Dati (UE 2016/679). Generato tramite la piattaforma UnicoGDPR.
    </div>
</body>
</html>
