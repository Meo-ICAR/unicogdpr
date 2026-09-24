<?php

use App\Http\Controllers\BpmBridgeController;
use App\Http\Controllers\CompanyPortal\DocumentDownloadController;
use App\Http\Controllers\CompanyPortal\DpiaReportController;
use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\VendorAuditQuestionnaireController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/drive/files', [GoogleDriveController::class, 'listFiles']);
// La rotta riceve l'ID del soggetto (es: l'agente) e il token di sicurezza nei parametri.
// Throttling per limitare il brute force sul token.
Route::get('/bpm-landing/{subjectId}', [BpmBridgeController::class, 'handle'])
    ->middleware('throttle:10,1')
    ->name('bpm.landing');

Route::get('/admin/manuale', function () {
    return response()->download(public_path('docs/Manuale Proforma.pdf'), 'Manuale Proforma.pdf');
})->name('filament.admin.pages.manuale');

Route::get('/admin/manuale-tecnico', function () {
    return response(file_get_contents(resource_path('manuals/manuale-tecnico.html')))
        ->header('Content-Type', 'text/html; charset=utf-8');
})->name('filament.admin.pages.manuale-tecnico');

Route::get('/admin/manuale-utente', function () {
    return response(file_get_contents(resource_path('docs/manuale-utente.html')))
        ->header('Content-Type', 'text/html; charset=utf-8');
})->name('filament.admin.pages.manuale-utente');

Route::get('/admin/prompt-vibe-coding', function () {
    return response(file_get_contents(resource_path('docs/prompt-vibe-coding.html')))
        ->header('Content-Type', 'text/html; charset=utf-8');
})->name('filament.admin.pages.prompt-vibe-coding');

// Portale pubblico (non autenticato) per la compilazione dei questionari
// fornitori Art. 28 GDPR: l'accesso è protetto dal token univoco nell'URL.
Route::get('/fornitori/questionario/{token}', [VendorAuditQuestionnaireController::class, 'show'])
    ->name('vendor-audit.show');
Route::post('/fornitori/questionario/{token}', [VendorAuditQuestionnaireController::class, 'submit'])
    ->name('vendor-audit.submit');

// Portale di sola consultazione per gli admin/titolari delle company: download
// del report PDF di una DPIA, protetto dal guard 'web' + verifica di appartenenza.
Route::get('/portale-dpia/{dpia}/report', DpiaReportController::class)
    ->middleware('auth')
    ->name('company-portal.dpia.report');

// Portale di sola consultazione: download di un documento aziendale (disco
// privato 'documenti'), protetto dal guard 'web' + verifica di appartenenza.
Route::get('/portale-documenti/{document}/download', DocumentDownloadController::class)
    ->middleware('auth')
    ->name('company-portal.document.download');
