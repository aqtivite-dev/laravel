<?php

namespace Aqtivite\Laravel\TokenStore;

use Aqtivite\Laravel\Contracts\TokenStoreInterface;
use Aqtivite\Laravel\Events\TokenExpired;
use Aqtivite\Php\Auth\Token;
use DateTimeImmutable;

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

        $token = new Token(
            accessToken: $data['access_token'],
            expiresAt: isset($data['expires_at']) ? new DateTimeImmutable($data['expires_at']) : null,
            tokenType: $data['token_type'] ?? 'Bearer',
            scopes: $data['scopes'] ?? [],
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
        $dir = dirname($this->path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->path, json_encode([
            'access_token' => $token->accessToken,
            'token_type' => $token->tokenType,
            'expires_at' => $token->expiresAt?->format(DATE_ATOM),
            'scopes' => $token->scopes,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

        // Restrict file permissions to owner only (read/write)
        chmod($this->path, 0600);
    }

    public function forget(): void
    {
        if (file_exists($this->path)) {
            unlink($this->path);
        }
    }
}
