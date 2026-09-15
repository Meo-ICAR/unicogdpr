<?php

use App\Http\Controllers\BpmBridgeController;
use App\Http\Controllers\VendorAuditQuestionnaireController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

// La rotta riceve l'ID del soggetto (es: l'agente) e il token di sicurezza nei parametri
Route::get('/bpm-landing/{subject_id}', [BpmBridgeController::class, 'handle'])
    ->name('bpm.landing');

Route::get('/admin/manuale', function () {
    return response()->download(public_path('docs/Manuale Proforma.pdf'), 'Manuale Proforma.pdf');
})->name('filament.admin.pages.manuale');

// Portale pubblico (non autenticato) per la compilazione dei questionari
// fornitori Art. 28 GDPR: l'accesso è protetto dal token univoco nell'URL.
Route::get('/fornitori/questionario/{token}', [VendorAuditQuestionnaireController::class, 'show'])
    ->name('vendor-audit.show');
Route::post('/fornitori/questionario/{token}', [VendorAuditQuestionnaireController::class, 'submit'])
    ->name('vendor-audit.submit');
