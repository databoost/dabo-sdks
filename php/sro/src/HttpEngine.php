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

    public function health(): array
    {
        $data = Transport::json('GET', $this->baseUrl.'/health', ['Accept: application/json'], null);

        return ['status' => (string) ($data['status'] ?? '')];
    }

    public function syncNatural(string $listId, array $items, ?int $expectedVersion = null): array
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

        return $this->request('POST', $this->listPath($listId).'/syncNatural', $this->withVersion([
            'items' => $payloadItems,
        ], $expectedVersion));
    }

    public function list(string $listId): array
    {
        return $this->request('GET', $this->listPath($listId), null);
    }

    public function jump(string $listId, string $itemId, int $toSequence, ?int $expectedVersion = null): array
    {
        return $this->request('POST', $this->listPath($listId).'/jump', $this->withVersion([
            'item_id' => $itemId,
            'to_sequence' => $toSequence,
        ], $expectedVersion));
    }

    public function reorder(
        string $listId,
        string $itemId,
        ?string $afterItemId,
        ?string $beforeItemId = null,
        ?int $expectedVersion = null,
    ): array {
        $body = ['item_id' => $itemId];
        if ($beforeItemId !== null) {
            $body['before_item_id'] = $beforeItemId;
        } else {
            $body['after_item_id'] = $afterItemId;
        }

        return $this->request('POST', $this->listPath($listId).'/reorder', $this->withVersion($body, $expectedVersion));
    }

    public function remove(string $listId, string $itemId, ?int $expectedVersion = null): array
    {
        return $this->request('POST', $this->listPath($listId).'/remove', $this->withVersion([
            'item_id' => $itemId,
        ], $expectedVersion));
    }

    public function resetSticky(string $listId, string $itemId, ?int $expectedVersion = null): array
    {
        return $this->request('POST', $this->listPath($listId).'/resetSticky', $this->withVersion([
            'item_id' => $itemId,
        ], $expectedVersion));
    }

    public function resetStickies(string $listId, ?int $expectedVersion = null): array
    {
        $body = $expectedVersion === null ? null : ['expected_version' => $expectedVersion];

        return $this->request('POST', $this->listPath($listId).'/resetStickies', $body);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    private function withVersion(array $body, ?int $expectedVersion): array
    {
        if ($expectedVersion !== null) {
            $body['expected_version'] = $expectedVersion;
        }

        return $body;
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

        $data = Transport::json($method, $this->baseUrl.$path, $headers, $payload);
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
}
