<?php

namespace Tests\Feature;

use App\Enums\DsarStatus;
use App\Models\Company;
use App\Models\DataSubjectRequest;
use App\Models\User;
use App\Notifications\DpoAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckDsarDeadlinesTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifies_dpo_about_overdue_and_expiring_requests(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $company = Company::factory()->create();

        DataSubjectRequest::factory()->for($company)->create([
            'status' => DsarStatus::Received->value,
            'deadline_at' => now()->subDays(2),
        ]);
        DataSubjectRequest::factory()->for($company)->create([
            'status' => DsarStatus::InProgress->value,
            'deadline_at' => now()->addDay(),
        ]);
        DataSubjectRequest::factory()->for($company)->create([
            'status' => DsarStatus::Completed->value,
            'deadline_at' => now()->subDays(5),
        ]);

        $this->artisan('dsar:deadline-check --days=3')->assertSuccessful();

        Notification::assertSentTo($user, DpoAlert::class, fn (DpoAlert $n) => str_contains($n->intro, '1 richieste scadute')
            && str_contains($n->intro, '1 in scadenza'));
    }

    public function test_no_notification_when_nothing_is_due(): void
    {
        Notification::fake();
        User::factory()->create();
        $company = Company::factory()->create();

        DataSubjectRequest::factory()->for($company)->create([
            'status' => DsarStatus::Received->value,
            'deadline_at' => now()->addDays(20),
        ]);

        $this->artisan('dsar:deadline-check --days=3')->assertSuccessful();

        Notification::assertNothingSent();
    }
}
