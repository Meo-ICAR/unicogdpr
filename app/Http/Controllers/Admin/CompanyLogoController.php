<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serve inline (non in download) il logo aziendale usato nello switcher
 * tenant di Filament (vedi Company::getFilamentAvatarUrl()). Il file resta
 * sul disco privato del Document sottostante: nessuna eccezione "pubblica"
 * per il logo, protetto dalla stessa autenticazione di sessione del pannello.
 */
class CompanyLogoController extends Controller
{
    public function __invoke(Company $company): BinaryFileResponse
    {
        $media = $company->logoDocument()?->getFirstMedia('documents');

        abort_if($media === null, 404);

        return response()->file($media->getPath(), [
            'Content-Type' => $media->mime_type,
        ]);
    }
}
