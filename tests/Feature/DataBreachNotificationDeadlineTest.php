<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\DataBreach;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataBreachNotificationDeadlineTest extends TestCase
{
    use RefreshDatabase;

    private function breach(array $attributes = []): DataBreach
    {
        return DataBreach::create(array_merge([
            'company_id' => Company::factory()->create()->id,
            'name' => 'Incidente di test',
            'severity' => 'high',
            'status' => 'investigating',
            'discovered_at' => now(),
            'is_notifiable_to_authority' => true,
            'is_notifiable_to_subjects' => true,
        ], $attributes));
    }

    public function test_deadline_is_72_hours_after_discovery(): void
    {
        $breach = $this->breach(['discovered_at' => now()]);

        $this->assertEqualsWithDelta(72, now()->diffInHours($breach->authorityNotificationDeadline()), 1);
    }

    public function test_state_transitions(): void
    {
        $this->assertSame('on_track', $this->breach(['discovered_at' => now()->subHours(10)])->authorityNotificationState());
        $this->assertSame('due_soon', $this->breach(['discovered_at' => now()->subHours(65)])->authorityNotificationState());
        $this->assertSame('overdue', $this->breach(['discovered_at' => now()->subHours(80)])->authorityNotificationState());
        $this->assertSame('done', $this->breach(['authority_notified_at' => now()])->authorityNotificationState());
        $this->assertSame('not_required', $this->breach(['is_notifiable_to_authority' => false])->authorityNotificationState());
    }
}
