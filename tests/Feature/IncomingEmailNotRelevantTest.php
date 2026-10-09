<?php

namespace Tests\Feature;

use App\Enums\EmailClassification;
use App\Filament\Resources\IncomingEmails\Pages\ListIncomingEmails;
use App\Filament\Resources\IncomingEmails\Pages\ViewIncomingEmail;
use App\Filament\Widgets\UnreadEmailsWidget;
use App\Models\Company;
use App\Models\IncomingEmail;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IncomingEmailNotRelevantTest extends TestCase
{
    use RefreshDatabase;

    private function actAsDpoOf(Company $company): void
    {
        $user = User::factory()->create();
        $user->companies()->attach($company, ['role' => 'dpo']);

        $this->actingAs($user);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($company);
    }

    public function test_row_action_classifies_the_email_as_not_relevant_and_reads_it(): void
    {
        $company = Company::factory()->create();
        $email = IncomingEmail::factory()->for($company)->create();

        $this->actAsDpoOf($company);

        Livewire::test(ViewIncomingEmail::class, ['record' => $email->id])
            ->callAction('markNotRelevant')
            ->assertNotified();

        $email->refresh();
        $this->assertSame(EmailClassification::NotRelevant, $email->classification);
        $this->assertTrue($email->is_read);
    }

    public function test_not_relevant_emails_are_hidden_by_default_in_widget_and_list(): void
    {
        $company = Company::factory()->create();
        $relevant = IncomingEmail::factory()->for($company)->create();
        $notRelevant = IncomingEmail::factory()->for($company)->classifiedAs(EmailClassification::NotRelevant)->create();

        $this->actAsDpoOf($company);

        Livewire::test(UnreadEmailsWidget::class)
            ->assertCanSeeTableRecords([$relevant])
            ->assertCanNotSeeTableRecords([$notRelevant]);

        Livewire::test(ListIncomingEmails::class)
            ->assertCanSeeTableRecords([$relevant])
            ->assertCanNotSeeTableRecords([$notRelevant]);
    }

    public function test_bulk_action_marks_selected_emails_as_not_relevant(): void
    {
        $company = Company::factory()->create();
        $emails = IncomingEmail::factory()->for($company)->count(2)->create();

        $this->actAsDpoOf($company);

        Livewire::test(UnreadEmailsWidget::class)
            ->selectTableRecords($emails->pluck('id')->all())
            ->callAction(TestAction::make('markAsNotRelevant')->table()->bulk())
            ->assertNotified();

        $this->assertSame(2, IncomingEmail::where('classification', EmailClassification::NotRelevant->value)->count());
        $this->assertSame(0, IncomingEmail::unread()->count());
    }
}
