<?php

namespace Tests\Feature;

use App\Models\MailAccount;
use App\Services\Mail\ImapConnectionFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\Token;
use Mockery;
use Tests\TestCase;
use Webklex\PHPIMAP\Client;

class ImapConnectionFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_make_returns_configured_client_for_password_account(): void
    {
        $account = MailAccount::factory()->create();

        $client = (new ImapConnectionFactory)->make($account);

        $this->assertInstanceOf(Client::class, $client);
        $this->assertSame('imap.example.com', $client->host);
    }

    public function test_refreshes_expired_oauth_token_via_socialite(): void
    {
        $account = MailAccount::factory()->oauth('google')->create();

        $driver = Mockery::mock();
        $driver->shouldReceive('stateless')->andReturnSelf();
        $driver->shouldReceive('refreshToken')
            ->once()
            ->with('stored-refresh-token')
            ->andReturn(new Token('new-access-token', 'new-refresh-token', 3600, []));

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($driver);

        (new ImapConnectionFactory)->refreshTokenIfNeeded($account);

        $account->refresh();
        $this->assertSame('new-access-token', $account->access_token);
        $this->assertSame('new-refresh-token', $account->refresh_token);
        $this->assertTrue($account->token_expires_at->isFuture());
    }

    public function test_does_not_refresh_when_token_still_valid(): void
    {
        $account = MailAccount::factory()->oauth('google')->create([
            'token_expires_at' => now()->addHour(),
        ]);

        // Nessuna aspettativa su Socialite: se venisse chiamato il test fallirebbe.
        (new ImapConnectionFactory)->refreshTokenIfNeeded($account);

        $this->assertSame('old-access-token', $account->fresh()->access_token);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
