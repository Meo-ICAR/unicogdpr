<?php

namespace App\Services\Mail;

use App\Contracts\ImapConnector;
use App\Models\MailAccount;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\Token;
use RuntimeException;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;

/**
 * Costruisce un client IMAP a partire da una MailAccount, gestendo sia la
 * Basic Auth sia OAuth 2.0 (Google, Microsoft) con rinnovo automatico
 * dell'access token quando è scaduto o in scadenza.
 */
class ImapConnectionFactory implements ImapConnector
{
    private ClientManager $clientManager;

    public function __construct(?ClientManager $clientManager = null)
    {
        $this->clientManager = $clientManager ?? new ClientManager;
    }

    /**
     * Restituisce un client IMAP configurato ma NON connesso: la connessione
     * resta responsabilità del chiamante.
     */
    public function make(MailAccount $account): Client
    {
        if ($account->auth_type === 'oauth2') {
            $this->refreshTokenIfNeeded($account);
        }

        return $this->clientManager->make([
            'host' => $account->imap_host,
            'port' => $account->imap_port,
            'encryption' => $this->normalizeEncryption($account->imap_encryption),
            'validate_cert' => true,
            'username' => $account->imap_username,
            'password' => $account->auth_type === 'oauth2' ? $account->access_token : $account->imap_password,
            'protocol' => 'imap',
            'authentication' => $account->auth_type === 'oauth2' ? 'oauth' : null,
        ]);
    }

    /**
     * Rinnova l'access token OAuth2 tramite il refresh token se necessario,
     * persistendo i nuovi valori (colonne già cifrate via cast 'encrypted').
     */
    public function refreshTokenIfNeeded(MailAccount $account): void
    {
        if ($account->auth_type !== 'oauth2' || ! $account->isTokenExpired()) {
            return;
        }

        if (blank($account->refresh_token)) {
            throw new RuntimeException(
                "MailAccount #{$account->id}: access token OAuth2 scaduto e nessun refresh token disponibile."
            );
        }

        $provider = $account->provider ?: 'google';

        /** @var Token $token */
        $token = Socialite::driver($provider)
            ->stateless()
            ->refreshToken($account->refresh_token);

        $account->forceFill([
            'access_token' => $token->token,
            'refresh_token' => $token->refreshToken ?: $account->refresh_token,
            'token_expires_at' => now()->addSeconds((int) $token->expiresIn),
        ])->save();
    }

    private function normalizeEncryption(?string $encryption): string|false
    {
        return match ($encryption) {
            null, '', 'none', 'false' => false,
            default => $encryption,
        };
    }
}
