<?php

namespace Aqtivite\Laravel\TokenStore;

use Aqtivite\Laravel\Contracts\TokenStoreInterface;
use Aqtivite\Laravel\Events\TokenExpired;
use Aqtivite\Php\Auth\Token;
use DateTimeImmutable;
use Illuminate\Support\Facades\Cache;

class CacheTokenStore implements TokenStoreInterface
{
    public function __construct(
        private readonly string $key = 'aqtivite:token',
    ) {}

    public function get(): ?Token
    {
        $data = Cache::get($this->key);

        if (! is_array($data) || empty($data['access_token'])) {
            return null;
        }

        $token = new Token(
            accessToken: $data['access_token'],
            expiresAt: isset($data['expires_at']) ? new DateTimeImmutable($data['expires_at']) : null,
            tokenType: $data['token_type'] ?? 'Bearer',
        );

        if ($token->isExpired(0)) {
            TokenExpired::dispatch($token);
            $this->forget();
            return null;
        }

        return $token;
    }

    public function put(Token $token): void
    {
        $data = [
            'access_token' => $token->accessToken,
            'token_type' => $token->tokenType,
            'expires_at' => $token->expiresAt?->format(DATE_ATOM),
        ];

        $token->expiresAt
            ? Cache::put($this->key, $data, $token->expiresAt)
            : Cache::forever($this->key, $data);
    }

    public function forget(): void
    {
        Cache::forget($this->key);
    }
}
