<?php
// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.

declare(strict_types=1);

namespace Databoost\Sro;

use InvalidArgumentException;
use RuntimeException;

/**
 * Thin HTTP client for the SRO service. Never sees major.minor.
 *
 * $baseUrl is required — there is no localhost or production default.
 * Production: https://sro.databoost.com
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

    public function syncNatural(string $listId, array $items): array
    {
        $payloadItems = [];
        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['id'])) {
                throw new InvalidArgumentException('syncNatural items must be {id, sort_key?, sort_data_type?}.');
            }
            $payloadItems[] = [
                'id' => (string) $item['id'],
                'sort_key' => $item['sort_key'] ?? null,
                'sort_data_type' => $item['sort_data_type'] ?? null,
            ];
        }

        return $this->request('POST', $this->listPath($listId).'/syncNatural', [
            'items' => $payloadItems,
        ]);
    }

    public function list(string $listId): array
    {
        return $this->request('GET', $this->listPath($listId), null);
    }

    public function jump(string $listId, string $itemId, int $toSequence): array
    {
        return $this->request('POST', $this->listPath($listId).'/jump', [
            'item_id' => $itemId,
            'to_sequence' => $toSequence,
        ]);
    }

    public function reorder(string $listId, string $itemId, ?string $afterItemId): array
    {
        return $this->request('POST', $this->listPath($listId).'/reorder', [
            'item_id' => $itemId,
            'after_item_id' => $afterItemId,
        ]);
    }

    public function remove(string $listId, string $itemId): array
    {
        return $this->request('POST', $this->listPath($listId).'/remove', [
            'item_id' => $itemId,
        ]);
    }

    public function resetSticky(string $listId, string $itemId): array
    {
        return $this->request('POST', $this->listPath($listId).'/resetSticky', [
            'item_id' => $itemId,
        ]);
    }

    public function resetStickies(string $listId): array
    {
        return $this->request('POST', $this->listPath($listId).'/resetStickies', null);
    }

    private function listPath(string $listId): string
    {
        return '/v1/tenants/'.rawurlencode($this->tenantId).'/lists/'.rawurlencode($listId);
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @return list<SequenceRow>
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
            throw new RuntimeException('SRO HTTP response was not JSON.');
        }
        if (isset($data['error']) && is_array($data['error'])) {
            $msg = (string) ($data['error']['message'] ?? 'SRO HTTP error');
            throw new RuntimeException($msg);
        }
        if (! isset($data['items']) || ! is_array($data['items'])) {
            throw new RuntimeException('SRO HTTP response missing items.');
        }

        $rows = [];
        foreach ($data['items'] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $parsed = SequenceRow::fromApi($row);
            if ($parsed !== null) {
                $rows[] = $parsed;
            }
        }

        return $rows;
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
