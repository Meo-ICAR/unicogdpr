<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ExternalProcessorAudit;
use App\Notifications\DpoAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

/**
 * Portale pubblico (non autenticato) tramite cui un fornitore/responsabile
 * esterno compila il questionario di adeguatezza tecnico-organizzativa
 * (Art. 28 GDPR) collegato al link ricevuto via email. L'accesso è protetto
 * unicamente dall'imprevedibilità del token: nessun account è richiesto.
 */
class VendorAuditQuestionnaireController extends Controller
{
    public function show(string $token)
    {
        $audit = ExternalProcessorAudit::where('token', $token)->firstOrFail();

        abort_if($audit->isSubmitted(), 410, 'Il questionario è già stato inviato e non può essere modificato.');

        return view('vendor-audit.show', [
            'audit' => $audit->load('externalProcessor'),
        ]);
    }

    public function submit(Request $request, string $token)
    {
        $audit = ExternalProcessorAudit::where('token', $token)->firstOrFail();

        abort_if($audit->isSubmitted(), 410, 'Il questionario è già stato inviato e non può essere modificato.');

        $validated = $request->validate([
            'vendor_answers' => ['required', 'string', 'max:10000'],
            'evidence.*' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,png,jpg,jpeg'],
        ]);

        $audit->update([
            'vendor_answers' => $validated['vendor_answers'],
            'submitted_at' => now(),
            'status' => 'under_review',
        ]);

        foreach ($request->file('evidence', []) as $file) {
            $audit->addMedia($file)->toMediaCollection('audit_evidences');
        }

        $company = $audit->externalProcessor?->company;

        if ($company instanceof Company) {
            $recipients = $company->users()->wherePivotIn('role', ['dpo', 'admin'])->get();

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new DpoAlert(
                    subject: 'Questionario fornitore ricevuto',
                    intro: "Il fornitore \"{$audit->externalProcessor?->name}\" ha inviato le risposte al questionario \"{$audit->title}\".",
                    lines: ["Accedi alla scheda fornitore per la valutazione (Audit #{$audit->id})."],
                ));
            }
        }

        return view('vendor-audit.thank-you');
    }
}
