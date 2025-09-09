<?php

namespace YoApy\SDK;

class HmacSigner
{
    public static function sign(string $method, string $path, string $body, string $secretHex, ?int $timestamp = null, ?string $nonce = null): array
    {
        $timestamp = $timestamp ?? time();
        $nonce     = $nonce ?? bin2hex(random_bytes(12)); // 12 bytes -> 24 hex chars

        $bodyHash = hash('sha256', $body);

        $canonical = implode("\n", [
            strtoupper($method),
            $path,
            (string)$timestamp,
            $nonce,
            $bodyHash,
        ]);

        $secretBin = hex2bin($secretHex);
        if ($secretHex !== '' && $secretBin === false) {
            throw new \InvalidArgumentException('Invalid hex for YOAPY secret');
        }

        $raw = hash_hmac('sha256', $canonical, $secretBin ?? '', true);
        $signature = base64_encode($raw);

        return [
            'timestamp' => $timestamp,
            'nonce'     => $nonce,
            'bodyHash'  => $bodyHash,
            'canonical' => $canonical,
            'signature' => $signature,
        ];
    }

    public static function normalizePath(string $urlOrPath): string
    {
        if ($urlOrPath === '') { return '/'; }
        if ($urlOrPath[0] === '/') { return $urlOrPath; }

        $parts = parse_url($urlOrPath);
        $path = $parts['path'] ?? $urlOrPath;

        if ($path === '' || $path[0] !== '/') {
            $path = '/' . $path;
        }
        return $path;
    }
}
