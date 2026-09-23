<?php

namespace App\Providers;

use App\Contracts\ImapConnector;
use App\Models\Audit;
use App\Models\AuditChecklistEvaluation;
use App\Models\Branch;
use App\Models\ClientController;
use App\Models\Clienti;
use App\Models\Company;
use App\Models\ComplaintRegistry;
use App\Models\Document;
use App\Models\Employee;
use App\Models\ExternalProcessor;
use App\Models\Fornitore;
use App\Models\TrainingRecord;
use App\Models\Website;
use App\Services\Mail\ImapConnectionFactory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Google\GoogleExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Microsoft\MicrosoftExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ImapConnector::class, ImapConnectionFactory::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'audit' => Audit::class,
            'audit_checklist_evaluation' => AuditChecklistEvaluation::class,
            'branch' => Branch::class,
            'client_controller' => ClientController::class,
            'cliente' => Clienti::class,
            'company' => Company::class,
            'complaint' => ComplaintRegistry::class,
            'document' => Document::class,
            'employee' => Employee::class,
            'external_processor' => ExternalProcessor::class,
            'fornitore' => Fornitore::class,
            'training_record' => TrainingRecord::class,
            'website' => Website::class,
        ]);

        Event::listen(
            SocialiteWasCalled::class,
            [MicrosoftExtendSocialite::class, 'handle']
        );
        Event::listen(
            SocialiteWasCalled::class,
            [GoogleExtendSocialite::class, 'handle']
        );
    }
}
