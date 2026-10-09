<?php

namespace Tests\Feature;

use App\Filament\Widgets\AuditsOverviewWidget;
use App\Filament\Widgets\BreachSlaWidget;
use App\Filament\Widgets\DsarOverviewWidget;
use App\Filament\Widgets\HighRiskDpiaWidget;
use App\Filament\Widgets\UnreadEmailsWidget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DashboardWidgetsSortingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: class-string, 1: list<string>}>
     */
    public static function widgetColumns(): array
    {
        return [
            'email non lette' => [UnreadEmailsWidget::class, ['from_name', 'subject', 'company.name', 'classification', 'received_at']],
            'audit' => [AuditsOverviewWidget::class, ['protocol_number', 'company.name', 'auditable', 'auditor_name', 'status', 'scheduled_at', 'followup_date']],
            'dsar' => [DsarOverviewWidget::class, ['requester_name', 'company.name', 'request_type', 'status', 'received_at', 'deadline_at', 'completed_at']],
            'data breach' => [BreachSlaWidget::class, ['name', 'severity', 'discovered_at', 'authority_deadline', 'hours_left']],
            'dpia' => [HighRiskDpiaWidget::class, ['name', 'processing_activity', 'status', 'items_count', 'max_risk', 'next_review_date']],
        ];
    }

    /**
     * @param  class-string  $widget
     * @param  list<string>  $columns
     */
    #[DataProvider('widgetColumns')]
    public function test_every_column_is_sortable_in_both_directions(string $widget, array $columns): void
    {
        $this->actingAs(User::factory()->create());

        foreach ($columns as $column) {
            Livewire::test($widget)
                ->sortTable($column, 'asc')
                ->assertHasNoErrors()
                ->sortTable($column, 'desc')
                ->assertHasNoErrors();
        }
    }
}
