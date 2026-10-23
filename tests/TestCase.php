<?php

namespace Tests;

use App\Services\Drive\CompanyDriveFolderProvisioner;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Creare una company provisiona le cartelle su Google Drive, che chiede il file di credenziali: nei test non si chiama Drive.
        $this->mock(CompanyDriveFolderProvisioner::class)->shouldIgnoreMissing();
    }
}
