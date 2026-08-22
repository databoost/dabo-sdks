<?php
// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.

declare(strict_types=1);

namespace Databoost\SmoothCurve;

use RuntimeException;

/**
 * Thin HTTP client for the SmoothCurve service.
 *
 * $baseUrl is required — there is no localhost or production default.
 */
final class HttpEngine implements Engine
{
    public function __construct(
        private string $baseUrl,
        private string $apiToken,
        private string $tenantId,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function curve(array $body): array
    {
        return $this->request('POST', $this->path('curve'), $body);
    }

    public function map(array $body): array
    {
        return $this->request('POST', $this->path('map'), $body);
    }

    private function path(string $suffix): string
    {
        return '/v1/tenants/'.rawurlencode($this->tenantId).'/'.$suffix;
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, ?array $body): array
    {
        $url = $this->baseUrl.$path;
        $headers = [
            'Authorization: Bearer '.$this->apiToken,
            'X-Tenant-Id: '.$this->tenantId,
            'Accept: application/json',
        ];

        $payload = null;
        if ($body !== null) {
            $payload = json_encode($body, JSON_THROW_ON_ERROR);
            $headers[] = 'Content-Type: application/json';
        }

        if (function_exists('curl_init')) {
            $json = $this->curl($method, $url, $headers, $payload);
        } else {
            $json = $this->stream($method, $url, $headers, $payload);
        }

        $data = json_decode($json, true);
        if (! is_array($data)) {
            throw new RuntimeException('SmoothCurve HTTP response was not JSON.');
        }
        if (isset($data['error']) && is_array($data['error'])) {
            $msg = (string) ($data['error']['message'] ?? 'SmoothCurve HTTP error');
            throw new RuntimeException($msg);
        }

        return $data;
    }

    /**
     * @param  list<string>  $headers
     */
    private function curl(string $method, string $url, array $headers, ?string $payload): string
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
            throw new RuntimeException('SmoothCurve HTTP curl failed.');
        }
        if ($status >= 400) {
            $data = json_decode($raw, true);
            $msg = is_array($data) && isset($data['error']['message'])
                ? (string) $data['error']['message']
                : "SmoothCurve HTTP {$status}";
            throw new RuntimeException($msg);
        }

        return $raw;
    }

    /**
     * @param  list<string>  $headers
     */
    private function stream(string $method, string $url, array $headers, ?string $payload): string
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
            throw new RuntimeException('SmoothCurve HTTP request failed.');
        }
        $statusLine = $http_response_header[0] ?? '';
        if (preg_match('/\s(\d{3})\s/', $statusLine, $m) && (int) $m[1] >= 400) {
            $data = json_decode($raw, true);
            $msg = is_array($data) && isset($data['error']['message'])
                ? (string) $data['error']['message']
                : 'SmoothCurve HTTP error';
            throw new RuntimeException($msg);
        }

        return $raw;
    }
}
