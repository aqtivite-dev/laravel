<?php

namespace Aqtivite\Laravel\TokenStore;

use Aqtivite\Laravel\Contracts\TokenStoreInterface;
use Aqtivite\Laravel\Events\TokenExpired;
use Aqtivite\Php\Auth\Token;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class CacheTokenStore implements TokenStoreInterface
{
    public function __construct(
        private readonly string $key = 'aqtivite_token',
    ) {}

    public function get(): ?Token
    {
        $data = Cache::get($this->key);

        if (! is_array($data) || empty($data['access_token'])) {
            return null;
        }

        // Check if token is expired
        if (isset($data['created_at'], $data['expires_in'])) {
            $createdAt = Carbon::parse($data['created_at']);
            $expiresAt = $createdAt->addSeconds($data['expires_in']);

            if ($expiresAt->isPast()) {
                $token = new Token(
                    accessToken: $data['access_token'],
                    refreshToken: $data['refresh_token'] ?? null,
                    tokenType: $data['token_type'] ?? 'Bearer',
                    expiresIn: $data['expires_in'] ?? null,
                );

                TokenExpired::dispatch($token);
                $this->forget();
                return null;
            }
        }

        return new Token(
            accessToken: $data['access_token'],
            refreshToken: $data['refresh_token'] ?? null,
            tokenType: $data['token_type'] ?? 'Bearer',
            expiresIn: $data['expires_in'] ?? null,
        );
    }

    public function put(Token $token): void
    {
        $data = [
            'access_token' => $token->accessToken,
            'refresh_token' => $token->refreshToken,
            'token_type' => $token->tokenType,
            'expires_in' => $token->expiresIn,
            'created_at' => Carbon::now()->toIso8601String(),
        ];

        $token->expiresIn
            ? Cache::put($this->key, $data, $token->expiresIn)
            : Cache::forever($this->key, $data);
    }

    public function forget(): void
    {
        Cache::forget($this->key);
    }
}
