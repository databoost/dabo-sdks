<?php
// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.

declare(strict_types=1);

namespace Databoost\Sro;

use RuntimeException;

/**
 * Shared JSON HTTP helper for ranking and admin clients.
 */
final class Transport
{
    /**
     * @param  list<string>  $headers
     * @return array<string, mixed>
     */
    public static function json(string $method, string $url, array $headers, ?string $payload): array
    {
        if (function_exists('curl_init')) {
            $raw = self::curl($method, $url, $headers, $payload);
        } else {
            $raw = self::stream($method, $url, $headers, $payload);
        }

        $data = json_decode($raw, true);
        if (! is_array($data)) {
            throw new RuntimeException('SRO HTTP response was not JSON.');
        }
        if (isset($data['error']) && is_array($data['error'])) {
            $msg = (string) ($data['error']['message'] ?? 'SRO HTTP error');
            throw new RuntimeException($msg);
        }

        return $data;
    }

    /**
     * @param  list<string>  $headers
     */
    private static function curl(string $method, string $url, array $headers, ?string $payload): string
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new RuntimeException('Unable to init curl.');
        }
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }
        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($errno !== 0 || $raw === false) {
            throw new RuntimeException('SRO HTTP curl failed.');
        }
        if ($status >= 400) {
            $data = json_decode($raw, true);
            $msg = is_array($data) && isset($data['error']['message'])
                ? (string) $data['error']['message']
                : "SRO HTTP {$status}";
            throw new RuntimeException($msg);
        }

        return $raw;
    }

    /**
     * @param  list<string>  $headers
     */
    private static function stream(string $method, string $url, array $headers, ?string $payload): string
    {
        $opts = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'ignore_errors' => true,
                'timeout' => 30,
            ],
        ];
        if ($payload !== null) {
            $opts['http']['content'] = $payload;
        }
        $raw = file_get_contents($url, false, stream_context_create($opts));
        if ($raw === false) {
            throw new RuntimeException('SRO HTTP request failed.');
        }
        $statusLine = $http_response_header[0] ?? '';
        if (preg_match('/\s(\d{3})\s/', $statusLine, $m) && (int) $m[1] >= 400) {
            $data = json_decode($raw, true);
            $msg = is_array($data) && isset($data['error']['message'])
                ? (string) $data['error']['message']
                : 'SRO HTTP error';
            throw new RuntimeException($msg);
        }

        return $raw;
    }
}
