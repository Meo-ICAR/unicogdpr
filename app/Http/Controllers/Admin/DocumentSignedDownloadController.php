<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Download via URL firmata (Laravel signed route) di un documento — usato per
 * i download multipli non-ZIP: ogni file viene aperto dal browser con una
 * propria richiesta GET verso una URL già autorizzata al momento della
 * generazione (scadenza breve), senza bisogno di autenticazione di sessione
 * separata per ciascuna richiesta innescata via JS.
 */
class DocumentSignedDownloadController extends Controller
{
    public function __invoke(Document $document): BinaryFileResponse
    {
        $media = $document->getFirstMedia('documents');

        abort_if($media === null, 404);

        return response()->download($media->getPath(), $media->file_name);
    }
}
