<?php

namespace App\Filament\Widgets;

use App\Models\DataBreach;
use App\Models\DataProcessor;
use App\Models\DataSubjectRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GdprStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // 1. DSAR in sospeso (received / in_progress)
        $pendingDsarCount = DataSubjectRequest::whereIn('status', ['received', 'in_progress'])->count();

        // 2. DSAR in scadenza nei prossimi 7 giorni
        $expiringDsarCount = DataSubjectRequest::whereIn('status', ['received', 'in_progress'])
            ->whereNotNull('deadline_at')
            ->whereBetween('deadline_at', [now(), now()->addDays(7)])
            ->count();

        // 3. Data Breach aperti (investigating / contained)
        $openBreachesCount = DataBreach::whereIn('status', ['investigating', 'contained'])->count();

        // 4. Accordi DPA in scadenza nei prossimi 30 giorni
        $expiringDpaCount = DataProcessor::where('has_dpa_signed', true)
            ->whereNotNull('dpa_expires_at')
            ->whereBetween('dpa_expires_at', [now(), now()->addDays(30)])
            ->count();

        return [
            Stat::make('Richieste DSAR in Sospeso', $pendingDsarCount)
                ->description('Istanze Art. 15-22 da evadere')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendingDsarCount > 0 ? 'warning' : 'success')
                ->chart([7, 4, 6, 8, 5, $pendingDsarCount]),

            Stat::make('DSAR in Scadenza (< 7 gg)', $expiringDsarCount)
                ->description('Termine 30 gg ex Art. 12.3')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($expiringDsarCount > 0 ? 'danger' : 'success')
                ->chart([2, 3, 1, 4, $expiringDsarCount]),

            Stat::make('Data Breach Aperti', $openBreachesCount)
                ->description('Incidenti in indagine / contenimento')
                ->descriptionIcon('heroicon-o-fire')
                ->color($openBreachesCount > 0 ? 'danger' : 'success')
                ->chart([1, 0, 2, 1, $openBreachesCount]),

            Stat::make('Accordi DPA in Scadenza', $expiringDpaCount)
                ->description('Scadenza contratti Art. 28 nei 30 gg')
                ->descriptionIcon('heroicon-o-document-text')
                ->color($expiringDpaCount > 0 ? 'warning' : 'success')
                ->chart([1, 2, 1, 3, $expiringDpaCount]),
        ];
    }
}
