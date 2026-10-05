@php
    $company = $this->getCompany();
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Anagrafica azienda --}}
        <div class="fi-section-content-ctn rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-white/10">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">Anagrafica</h2>
            </div>
            <div class="grid grid-cols-1 gap-4 px-6 py-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Ragione sociale</div>
                    <div class="text-gray-950 dark:text-white">{{ $company->name }}</div>
                </div>
                <div>
                    <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Partita IVA</div>
                    <div class="text-gray-950 dark:text-white">{{ $company->vat_number ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Codice fiscale</div>
                    <div class="text-gray-950 dark:text-white">{{ $company->tax_code ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Indirizzo</div>
                    <div class="text-gray-950 dark:text-white">{{ $company->address ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Email</div>
                    <div class="text-gray-950 dark:text-white">{{ $company->email ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">PEC</div>
                    <div class="text-gray-950 dark:text-white">{{ $company->pec ?: '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Documenti aziendali --}}
        <div class="fi-section-content-ctn rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-white/10">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">Documenti aziendali</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Visura camerale, preventivo DPO, contratto, registrazioni, regolamento informatico e altri documenti caricati dal DPO.</p>
            </div>
            @if ($company->documents->isEmpty())
                <div class="p-6 text-sm text-gray-500 dark:text-gray-400">Nessun documento caricato.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="fi-ta-table w-full text-start">
                        <thead class="divide-y divide-gray-200 dark:divide-white/10">
                            <tr class="bg-gray-50 dark:bg-white/5">
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Tipo documento</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Nome</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Emissione</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Scadenza</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Firmato</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">File</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                            @foreach ($company->documents as $document)
                                @php
                                    $hasMedia = $document->getFirstMedia('documents') !== null;
                                    $url = $hasMedia ? route('company-portal.document.download', $document) : $document->document_url;
                                    $isExpired = $document->expires_at && $document->expires_at->isPast();
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-gray-950 dark:text-white">{{ $document->documentType?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-950 dark:text-white">{{ $document->name }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $document->emitted_at?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-4 py-3 {{ $isExpired ? 'font-semibold text-danger-600 dark:text-danger-400' : 'text-gray-500 dark:text-gray-400' }}">
                                        {{ $document->expires_at?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($document->is_signed)
                                            <span class="fi-badge inline-flex items-center gap-1 rounded-md bg-success-50 px-2 py-1 text-xs font-medium text-success-700 ring-1 ring-inset ring-success-600/10 dark:bg-success-400/10 dark:text-success-400">Firmato</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($url)
                                            <a href="{{ $url }}" target="_blank" class="text-primary-600 underline hover:text-primary-500 dark:text-primary-400">Apri</a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Registrazioni --}}
        <div class="fi-section-content-ctn rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-white/10">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">Registrazioni e autorizzazioni</h2>
            </div>
            @if ($company->registrations->isEmpty())
                <div class="p-6 text-sm text-gray-500 dark:text-gray-400">Nessuna registrazione presente.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="fi-ta-table w-full text-start">
                        <thead class="divide-y divide-gray-200 dark:divide-white/10">
                            <tr class="bg-gray-50 dark:bg-white/5">
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Denominazione</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Codice</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Validità</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Motivazione</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                            @foreach ($company->registrations as $registration)
                                <tr>
                                    <td class="px-4 py-3 text-gray-950 dark:text-white">{{ $registration->name }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $registration->code ?: '—' }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                        {{ $registration->start_at?->format('d/m/Y') ?? '—' }} @if($registration->end_at) → {{ $registration->end_at->format('d/m/Y') }} @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $registration->reason ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- DPIA --}}
        <div class="fi-section-content-ctn rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-white/10">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">Valutazioni d'impatto (DPIA)</h2>
            </div>
            @if ($company->dpias->isEmpty())
                <div class="p-6 text-sm text-gray-500 dark:text-gray-400">Nessuna DPIA presente.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="fi-ta-table w-full text-start">
                        <thead class="divide-y divide-gray-200 dark:divide-white/10">
                            <tr class="bg-gray-50 dark:bg-white/5">
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Nome</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Stato</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Completamento</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Prossima revisione</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Report</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                            @foreach ($company->dpias as $dpia)
                                <tr>
                                    <td class="px-4 py-3 text-gray-950 dark:text-white">{{ $dpia->name }}</td>
                                    <td class="px-4 py-3">
                                        <span @class([
                                            'fi-badge inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset',
                                            'bg-success-50 text-success-700 ring-success-600/10 dark:bg-success-400/10 dark:text-success-400' => $dpia->status === 'completed',
                                            'bg-warning-50 text-warning-700 ring-warning-600/10 dark:bg-warning-400/10 dark:text-warning-400' => $dpia->status === 'under_review',
                                            'bg-gray-50 text-gray-700 ring-gray-600/10 dark:bg-gray-400/10 dark:text-gray-400' => ! in_array($dpia->status, ['completed', 'under_review'], true),
                                        ])>{{ ucfirst(str_replace('_', ' ', $dpia->status)) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $dpia->completion_date?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $dpia->next_review_date?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('company-portal.dpia.report', $dpia) }}" class="text-primary-600 underline hover:text-primary-500 dark:text-primary-400">PDF</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Registro dei trattamenti --}}
        <div class="fi-section-content-ctn rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-white/10">
                <h2 class="text-base font-semibold text-gray-950 dark:text-white">Registro dei trattamenti</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Clicca su un trattamento per vederne il dettaglio completo — utile come estratto in caso di audit.</p>
            </div>
            @if ($company->processingActivities->isEmpty())
                <div class="p-6 text-sm text-gray-500 dark:text-gray-400">Nessun trattamento censito.</div>
            @else
                <div class="overflow-x-auto" x-data="{ open: null }">
                    <table class="fi-ta-table w-full text-start">
                        <thead class="divide-y divide-gray-200 dark:divide-white/10">
                            <tr class="bg-gray-50 dark:bg-white/5">
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Codice</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Trattamento</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Ruolo</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Extra-UE</th>
                                <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Stato</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                            @foreach ($company->processingActivities as $activity)
                                <tr class="cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5" @click="open = (open === {{ $activity->id }} ? null : {{ $activity->id }})">
                                    <td class="px-4 py-3 text-gray-950 dark:text-white">{{ $activity->code }}</td>
                                    <td class="px-4 py-3 text-gray-950 dark:text-white">{{ $activity->name }}</td>
                                    <td class="px-4 py-3">
                                        <span class="fi-badge inline-flex items-center gap-1 rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-600/10 dark:bg-gray-400/10 dark:text-gray-400">
                                            {{ $activity->role === 'controller' ? 'Titolare' : 'Responsabile' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($activity->has_third_country_transfers)
                                            <span class="fi-badge inline-flex items-center gap-1 rounded-md bg-warning-50 px-2 py-1 text-xs font-medium text-warning-700 ring-1 ring-inset ring-warning-600/10 dark:bg-warning-400/10 dark:text-warning-400">Sì</span>
                                        @else
                                            <span class="text-gray-400">No</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($activity->is_active)
                                            <span class="fi-badge inline-flex items-center gap-1 rounded-md bg-success-50 px-2 py-1 text-xs font-medium text-success-700 ring-1 ring-inset ring-success-600/10 dark:bg-success-400/10 dark:text-success-400">Attivo</span>
                                        @else
                                            <span class="fi-badge inline-flex items-center gap-1 rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-600/10 dark:bg-gray-400/10 dark:text-gray-400">Inattivo</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr x-show="open === {{ $activity->id }}" x-cloak>
                                    <td colspan="5" class="bg-gray-50 px-6 py-4 dark:bg-white/5">
                                        <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                                            <div>
                                                <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Finalità</dt>
                                                <dd class="text-gray-950 dark:text-white">{{ $activity->purposes ?: '—' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Base giuridica</dt>
                                                <dd class="text-gray-950 dark:text-white">{{ $activity->legal_basis ?: '—' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Categorie di interessati</dt>
                                                <dd class="text-gray-950 dark:text-white">{{ $activity->data_subject_categories ?: '—' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Categorie di dati</dt>
                                                <dd class="text-gray-950 dark:text-white">{{ $activity->privacyDataTypes->pluck('name')->implode(', ') ?: '—' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Destinatari</dt>
                                                <dd class="text-gray-950 dark:text-white">{{ $activity->recipients ?: '—' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Trasferimenti extra-UE</dt>
                                                <dd class="text-gray-950 dark:text-white">{{ $activity->third_countries_details ?: '—' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Tempi di conservazione</dt>
                                                <dd class="text-gray-950 dark:text-white">{{ $activity->retention_policy ?: '—' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Cliente per cui si opera come Responsabile</dt>
                                                <dd class="text-gray-950 dark:text-white">{{ $activity->clientController?->name ?? '—' }}</dd>
                                            </div>
                                            @if ($activity->notes)
                                                <div class="sm:col-span-2">
                                                    <dt class="text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Note</dt>
                                                    <dd class="text-gray-950 dark:text-white">{{ $activity->notes }}</dd>
                                                </div>
                                            @endif
                                        </dl>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
