<?php

declare(strict_types=1);

namespace Databoost\Sro;

/**
 * Thin PHP client factory for the SRO HTTP API.
 *
 * Ranking lives on the sidecar (the SRO HTTP API); this package only makes API calls.
 *
 * @example
 * $client = SroClient::http('http://127.0.0.1:8080', $token, 'lpp');
 * $client->syncNatural('bindery', [
 *     ['id' => '1', 'sort_key' => '2026-08-01', 'sort_data_type' => 'date'],
 * ]);
 * $rows = $client->list('bindery'); // SequenceRow id + sequence 1…n
 * $client->jump('bindery', '1', 3);
 */
final class SroClient
{
    public static function http(string $baseUrl, string $apiToken, string $tenantId): StatefulSroClient
    {
        return new StatefulSroClient(new HttpSroEngine($baseUrl, $apiToken, $tenantId));
    }
}
