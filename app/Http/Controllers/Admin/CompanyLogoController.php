<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serve inline (non in download) il logo aziendale usato nello switcher
 * tenant di Filament (vedi Company::getFilamentAvatarUrl()). Il file resta
 * sul disco privato del Document sottostante: nessuna eccezione "pubblica"
 * per il logo, protetto dalla stessa autenticazione di sessione del pannello.
 *
 * Lo storage locale non è condiviso tra ambienti (es. dev e produzione): se
 * il file fisico non è presente su questo server ma il Document risulta
 * sincronizzato su Google Drive (document_url), si effettua un redirect lì
 * invece di restituire 404.
 */
class CompanyLogoController extends Controller
{
    public function __invoke(Company $company): BinaryFileResponse|RedirectResponse
    {
        $document = $company->logoDocument();
        $media = $document?->getFirstMedia('documents');

        abort_if($media === null, 404);

        if (is_file($media->getPath())) {
            return response()->file($media->getPath(), [
                'Content-Type' => $media->mime_type,
            ]);
        }

        abort_if(blank($document->document_url), 404);

        return redirect()->away($document->document_url);
    }
}
