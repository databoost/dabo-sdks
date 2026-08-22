<?php
// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.

declare(strict_types=1);

namespace Databoost\Sro;

/**
 * Façade for the SRO HTTP API (state + ranking live on the service).
 *
 * @example
 * $client = Client::http('https://sro.databoost.com', $token, 'lpp');
 * $client->syncNatural('bindery', [
 *     ['id' => '1', 'sort_key' => '2026-08-01', 'sort_data_type' => 'date'],
 * ]);
 * $rows = $client->list('bindery'); // SequenceRow id + sequence + sticky
 * $client->jump('bindery', '1', 3);
 * $client->resetSticky('bindery', '1');
 */
final class Client
{
    public function __construct(
        private Engine $engine,
    ) {}

    /**
     * Build a Client over HttpEngine. $baseUrl is required (no default).
     */
    public static function http(string $baseUrl, string $apiToken, string $tenantId): self
    {
        return new self(new HttpEngine($baseUrl, $apiToken, $tenantId));
    }

    public function engine(): Engine
    {
        return $this->engine;
    }

    /**
     * @return array{status: string}
     */
    public function health(): array
    {
        return $this->engine->health();
    }

    /**
     * @param  list<array{id: string, sort_key?: ?string, sort_data_type?: ?string}>  $items
     * @return list<SequenceRow>
     */
    public function syncNatural(string $listId, array $items, ?int $expectedVersion = null): array
    {
        return $this->engine->syncNatural($listId, $items, $expectedVersion);
    }

    /**
     * @return list<SequenceRow>
     */
    public function list(string $listId): array
    {
        return $this->engine->list($listId);
    }

    /**
     * @return list<SequenceRow>
     */
    public function jump(string $listId, string $itemId, int $toSequence, ?int $expectedVersion = null): array
    {
        return $this->engine->jump($listId, $itemId, $toSequence, $expectedVersion);
    }

    /**
     * @return list<SequenceRow>
     */
    public function reorder(
        string $listId,
        string $itemId,
        ?string $afterItemId,
        ?string $beforeItemId = null,
        ?int $expectedVersion = null,
    ): array {
        return $this->engine->reorder($listId, $itemId, $afterItemId, $beforeItemId, $expectedVersion);
    }

    /**
     * @return list<SequenceRow>
     */
    public function remove(string $listId, string $itemId, ?int $expectedVersion = null): array
    {
        return $this->engine->remove($listId, $itemId, $expectedVersion);
    }

    /**
     * @return list<SequenceRow>
     */
    public function resetSticky(string $listId, string $itemId, ?int $expectedVersion = null): array
    {
        return $this->engine->resetSticky($listId, $itemId, $expectedVersion);
    }

    /**
     * @return list<SequenceRow>
     */
    public function resetStickies(string $listId, ?int $expectedVersion = null): array
    {
        return $this->engine->resetStickies($listId, $expectedVersion);
    }

    /**
     * Rows whose sequence or sticky flag changed (or that are new).
     *
     * @param  list<SequenceRow>  $prev
     * @param  list<SequenceRow>  $next
     * @return list<SequenceRow>
     */
    public function diffSequences(array $prev, array $next): array
    {
        $prevMap = [];
        foreach ($prev as $row) {
            $prevMap[$row->id] = $row;
        }

        $changed = [];
        foreach ($next as $row) {
            if (! isset($prevMap[$row->id])
                || $prevMap[$row->id]->sequence !== $row->sequence
                || $prevMap[$row->id]->sticky !== $row->sticky) {
                $changed[] = $row;
            }
        }

        return $changed;
    }
}
