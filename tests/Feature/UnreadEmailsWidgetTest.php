<?php

namespace Tests\Feature;

use App\Enums\EmailClassification;
use App\Filament\Resources\IncomingEmails\IncomingEmailResource;
use App\Filament\Widgets\UnreadEmailsWidget;
use App\Models\Company;
use App\Models\IncomingEmail;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UnreadEmailsWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_unread_emails_of_all_companies_by_default(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $unread = IncomingEmail::factory()->for($company)->create(['subject' => 'Email da leggere']);
        $read = IncomingEmail::factory()->for($company)->create(['subject' => 'Email già letta', 'is_read' => true]);
        $foreign = IncomingEmail::factory()->for($otherCompany)->create(['subject' => 'Email di altra azienda']);

        $this->actingAs(User::factory()->create());

        Livewire::test(UnreadEmailsWidget::class)
            ->assertCanSeeTableRecords([$unread, $foreign])
            ->assertCanNotSeeTableRecords([$read])
            ->removeTableFilter('unread')
            ->assertCanSeeTableRecords([$unread, $read, $foreign]);
    }

    public function test_bulk_action_marks_selected_emails_as_read(): void
    {
        $company = Company::factory()->create();

        $user = User::factory()->create();
        $user->companies()->attach($company, ['role' => 'dpo']);

        $emails = IncomingEmail::factory()->for($company)->count(3)->create();

        $this->actingAs($user);

        Livewire::test(UnreadEmailsWidget::class)
            ->selectTableRecords($emails->pluck('id')->all())
            ->callAction(TestAction::make('markAsRead')->table()->bulk())
            ->assertNotified();

        $this->assertSame(0, IncomingEmail::unread()->count());
    }

    public function test_row_link_opens_the_email_view_for_its_company(): void
    {
        $company = Company::factory()->create();

        $user = User::factory()->create();
        $user->companies()->attach($company, ['role' => 'dpo']);

        $email = IncomingEmail::factory()->for($company)->create();

        $this->actingAs($user);

        Livewire::test(UnreadEmailsWidget::class)
            ->assertSee(IncomingEmailResource::getUrl('view', ['record' => $email], tenant: $company));
    }

    public function test_ignores_the_current_tenant_scope_and_shows_all_companies(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $user = User::factory()->create();
        $user->companies()->attach($company, ['role' => 'dpo']);

        $own = IncomingEmail::factory()->for($company)->create();
        $foreign = IncomingEmail::factory()->for($otherCompany)->create();

        $this->actingAs($user);

        $panel = Filament::getPanel('admin');
        Filament::setCurrentPanel($panel);
        IncomingEmailResource::registerTenancyModelGlobalScope($panel);
        Filament::setTenant($company);

        $this->assertNotContains($foreign->id, IncomingEmail::query()->pluck('id'));

        Livewire::test(UnreadEmailsWidget::class)
            ->assertCanSeeTableRecords([$own, $foreign])
            ->selectTableRecords([$foreign->id])
            ->callAction(TestAction::make('markAsRead')->table()->bulk());

        $this->assertTrue($foreign->fresh()->is_read);
    }

    public function test_excludes_provider_notifications_by_default(): void
    {
        $company = Company::factory()->create();

        $notification = IncomingEmail::factory()->for($company)->classifiedAs(EmailClassification::ProviderNotification)->create();
        $regular = IncomingEmail::factory()->for($company)->classifiedAs(EmailClassification::Complaint)->create();

        $this->actingAs(User::factory()->create());

        Livewire::test(UnreadEmailsWidget::class)
            ->assertCanSeeTableRecords([$regular])
            ->assertCanNotSeeTableRecords([$notification])
            ->removeTableFilter('classification')
            ->assertCanSeeTableRecords([$regular, $notification]);
    }

    public function test_can_filter_by_company(): void
    {
        $company = Company::factory()->create();
        $otherCompany = Company::factory()->create();

        $own = IncomingEmail::factory()->for($company)->create();
        $other = IncomingEmail::factory()->for($otherCompany)->create();

        $this->actingAs(User::factory()->create());

        Livewire::test(UnreadEmailsWidget::class)
            ->assertCanSeeTableRecords([$own, $other])
            ->filterTable('company_id', $otherCompany->id)
            ->assertCanSeeTableRecords([$other])
            ->assertCanNotSeeTableRecords([$own]);
    }

    public function test_columns_start_with_date_class_company_and_subject(): void
    {
        $this->actingAs(User::factory()->create());

        $columns = array_keys(Livewire::test(UnreadEmailsWidget::class)->instance()->getTable()->getColumns());

        $this->assertSame(['received_at', 'classification', 'company.name', 'subject'], array_slice($columns, 0, 4));
    }
}
