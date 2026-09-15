<x-filament-panels::page>
    <div class="fi-section-content-ctn rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        @if (empty($rows))
            <div class="p-6 text-sm text-gray-500 dark:text-gray-400">
                Nessuna azienda del Gruppo su cui hai un ruolo di supervisione (DPO/Admin).
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="fi-ta-table w-full text-start">
                    <thead class="divide-y divide-gray-200 dark:divide-white/10">
                        <tr class="bg-gray-50 dark:bg-white/5">
                            <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Società</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Data Breach — SLA 72h</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">DPIA in attesa di parere</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">DSAR aperte</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Fornitori DPA in scadenza</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach ($rows as $row)
                            <tr class="{{ $row['has_alerts'] ? 'bg-danger-50/50 dark:bg-danger-500/5' : '' }}">
                                <td class="px-4 py-3 font-medium text-gray-950 dark:text-white">
                                    {{ $row['company']->name }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($row['breaches_overdue'] > 0)
                                        <span class="fi-badge inline-flex items-center gap-1 rounded-md bg-danger-50 px-2 py-1 text-xs font-medium text-danger-700 ring-1 ring-inset ring-danger-600/10 dark:bg-danger-400/10 dark:text-danger-400">
                                            {{ $row['breaches_overdue'] }} scaduti
                                        </span>
                                    @endif
                                    @if ($row['breaches_due_soon'] > 0)
                                        <span class="fi-badge inline-flex items-center gap-1 rounded-md bg-warning-50 px-2 py-1 text-xs font-medium text-warning-700 ring-1 ring-inset ring-warning-600/10 dark:bg-warning-400/10 dark:text-warning-400">
                                            {{ $row['breaches_due_soon'] }} in scadenza
                                        </span>
                                    @endif
                                    @if ($row['breaches_overdue'] === 0 && $row['breaches_due_soon'] === 0)
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($row['dpia_pending_signoff'] > 0)
                                        <span class="fi-badge inline-flex items-center gap-1 rounded-md bg-warning-50 px-2 py-1 text-xs font-medium text-warning-700 ring-1 ring-inset ring-warning-600/10 dark:bg-warning-400/10 dark:text-warning-400">
                                            {{ $row['dpia_pending_signoff'] }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($row['dsar_overdue'] > 0)
                                        <span class="fi-badge inline-flex items-center gap-1 rounded-md bg-danger-50 px-2 py-1 text-xs font-medium text-danger-700 ring-1 ring-inset ring-danger-600/10 dark:bg-danger-400/10 dark:text-danger-400">
                                            {{ $row['dsar_overdue'] }} scadute
                                        </span>
                                    @endif
                                    @if ($row['dsar_expiring_soon'] > 0)
                                        <span class="fi-badge inline-flex items-center gap-1 rounded-md bg-warning-50 px-2 py-1 text-xs font-medium text-warning-700 ring-1 ring-inset ring-warning-600/10 dark:bg-warning-400/10 dark:text-warning-400">
                                            {{ $row['dsar_expiring_soon'] }} in scadenza
                                        </span>
                                    @endif
                                    @if ($row['dsar_overdue'] === 0 && $row['dsar_expiring_soon'] === 0)
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($row['vendors_dpa_expiring'] > 0)
                                        <span class="fi-badge inline-flex items-center gap-1 rounded-md bg-warning-50 px-2 py-1 text-xs font-medium text-warning-700 ring-1 ring-inset ring-warning-600/10 dark:bg-warning-400/10 dark:text-warning-400">
                                            {{ $row['vendors_dpa_expiring'] }}
                                        </span>
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
</x-filament-panels::page>
