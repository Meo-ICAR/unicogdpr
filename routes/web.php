<?php

use App\Http\Controllers\BpmBridgeController;
use App\Models\Document;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

Route::redirect('/', '/admin');

// La rotta riceve l'ID del soggetto (es: l'agente) e il token di sicurezza nei parametri
Route::get('/bpm-landing/{subject_id}', [BpmBridgeController::class, 'handle'])
    ->name('bpm.landing');
