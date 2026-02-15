<?php

namespace Aqtivite\Laravel\TokenStore;

use Aqtivite\Laravel\Contracts\TokenStoreInterface;
use Aqtivite\Php\Auth\Token;
use Illuminate\Support\Carbon;

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

        // Check if token is expired
        if (isset($data['created_at'], $data['expires_in'])) {
            $createdAt = Carbon::parse($data['created_at']);
            $expiresAt = $createdAt->addSeconds($data['expires_in']);

            if ($expiresAt->isPast()) {
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
        $dir = dirname($this->path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->path, json_encode([
            'access_token' => $token->accessToken,
            'refresh_token' => $token->refreshToken,
            'token_type' => $token->tokenType,
            'expires_in' => $token->expiresIn,
            'created_at' => Carbon::now()->toIso8601String(),
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
