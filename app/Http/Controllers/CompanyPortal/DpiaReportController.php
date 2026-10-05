<?php

namespace App\Http\Controllers\CompanyPortal;

use App\Http\Controllers\Controller;
use App\Models\Dpia;
use App\Services\DocumentGeneratorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Download del report PDF di una DPIA dal portale di sola consultazione
 * degli admin/titolari delle company. Riusa lo stesso generatore già
 * impiegato dal pannello DPO (App\Filament\Resources\Dpias\Tables\DpiasTable).
 */
class DpiaReportController extends Controller
{
    public function __invoke(Dpia $dpia, DocumentGeneratorService $service): Response
    {
        abort_unless(
            Auth::user()?->companies()->whereKey($dpia->company_id)->exists(),
            403
        );

        $pdf = $service->generateDpiaReport($dpia);
        $fileName = 'DPIA_'.Str::slug($dpia->name).'.pdf';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $fileName,
            ['Content-Type' => 'application/pdf']
        );
    }
}
