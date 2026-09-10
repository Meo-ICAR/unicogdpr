<?php

namespace Tests\Feature;

use App\Models\MailAccount;
use App\Models\User;
use App\Notifications\DpoAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckMailAccountsHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_flags_stale_mailboxes(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        MailAccount::factory()->create(['last_synced_at' => now()->subDays(3)]);
        MailAccount::factory()->create(['last_synced_at' => now()->subMinutes(5)]);
        MailAccount::factory()->create(['is_active' => false, 'last_synced_at' => now()->subYear()]);

        $this->artisan('mail:health-check')->assertSuccessful();

        Notification::assertSentTo($user, DpoAlert::class, fn (DpoAlert $n) => str_contains($n->intro, '1 casella'));
    }

    public function test_healthy_mailboxes_produce_no_notification(): void
    {
        Notification::fake();
        User::factory()->create();
        MailAccount::factory()->create(['last_synced_at' => now()->subHour()]);

        $this->artisan('mail:health-check')->assertSuccessful();

        Notification::assertNothingSent();
    }
}
