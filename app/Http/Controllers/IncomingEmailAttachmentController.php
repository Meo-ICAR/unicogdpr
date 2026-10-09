<?php

namespace App\Http\Controllers;

use App\Models\IncomingEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serve un allegato di una email in arrivo dal disco privato: inline per i
 * formati che il browser può mostrare in sicurezza (PDF, immagini, testo),
 * altrimenti (o con ?download=1) come download.
 */
class IncomingEmailAttachmentController extends Controller
{
    private const INLINE_MIME_TYPES = [
        'application/pdf',
        'image/png',
        'image/jpeg',
        'image/gif',
        'image/webp',
        'text/plain',
    ];

    public function __invoke(Request $request, Media $media): Response
    {
        abort_unless(
            $media->model_type === (new IncomingEmail)->getMorphClass()
                && $media->collection_name === 'email_attachments',
            404,
        );

        $disk = Storage::disk($media->disk);
        $path = $media->getPathRelativeToRoot();

        abort_unless($disk->exists($path), 404);

        $inline = ! $request->boolean('download') && in_array($media->mime_type, self::INLINE_MIME_TYPES, true);

        return $disk->response($path, $media->file_name, [
            'Content-Type' => $media->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline'",
        ], $inline ? 'inline' : 'attachment');
    }
}
