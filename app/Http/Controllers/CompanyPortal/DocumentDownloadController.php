<?php

namespace App\Http\Controllers\CompanyPortal;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Download di un documento aziendale dal portale di sola consultazione degli
 * admin/titolari delle company. Il collection 'documents' di Document vive
 * sul disco privato 'documenti' (non servito pubblicamente), quindi serve
 * un controller autenticato che verifichi l'appartenenza alla company.
 */
class DocumentDownloadController extends Controller
{
    public function __invoke(Document $document): BinaryFileResponse
    {
        abort_unless(
            Auth::user()?->companies()->whereKey($document->company_id)->exists(),
            403
        );

        $media = $document->getFirstMedia('documents');

        abort_if($media === null, 404);

        return response()->download($media->getPath(), $media->file_name);
    }
}
