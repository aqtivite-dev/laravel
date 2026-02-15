<?php

namespace Aqtivite\Laravel\TokenStore;

use Aqtivite\Laravel\Contracts\TokenStoreInterface;
use Aqtivite\Php\Auth\Token;

class FileTokenStore implements TokenStoreInterface
{
    public function __construct(
        private readonly string $path,
    ) {}

    public function get(): ?Token
    {
        if (! file_exists($this->path)) {
            return null;
        }

        $data = json_decode(file_get_contents($this->path), true);

        if (! is_array($data) || empty($data['access_token'])) {
            return null;
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
        $dir = dirname($this->path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->path, json_encode([
            'access_token' => $token->accessToken,
            'refresh_token' => $token->refreshToken,
            'token_type' => $token->tokenType,
            'expires_in' => $token->expiresIn,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }
}
