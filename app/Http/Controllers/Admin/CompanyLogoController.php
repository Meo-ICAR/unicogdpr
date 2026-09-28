<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\Drive\DocumentDriveSync;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Serve inline (non in download) il logo aziendale usato nello switcher
 * tenant di Filament (vedi Company::getFilamentAvatarUrl()). Il file resta
 * sul disco privato del Document sottostante: nessuna eccezione "pubblica"
 * per il logo, protetto dalla stessa autenticazione di sessione del pannello.
 *
 * Lo storage locale non è condiviso tra ambienti (es. dev e produzione): se
 * il file fisico non è presente su questo server ma il Document è
 * sincronizzato su Google Drive (app_id), il contenuto viene scaricato al
 * volo dal service account e servito come proxy. Non si fa un redirect al
 * link Drive (document_url): è la pagina viewer HTML di Drive, non
 * un'immagine diretta, e comunque richiederebbe che il file sia condiviso
 * pubblicamente.
 */
class CompanyLogoController extends Controller
{
    public function __invoke(Company $company, DocumentDriveSync $driveSync): BinaryFileResponse|Response
    {
        $document = $company->logoDocument();
        $media = $document?->getFirstMedia('documents');

        abort_if($media === null, 404);

        if (is_file($media->getPath())) {
            return response()->file($media->getPath(), [
                'Content-Type' => $media->mime_type,
            ]);
        }

        abort_if(blank($document->app_id), 404);

        $contents = $driveSync->downloadFileContents($document->app_id);

        return response($contents, 200, [
            'Content-Type' => $media->mime_type,
        ]);
    }
}
