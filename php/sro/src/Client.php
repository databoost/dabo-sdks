<?php

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
 * $rows = $client->list('bindery'); // SequenceRow id + sequence 1…n
 * $client->jump('bindery', '1', 3);
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
     * @param  list<array{id: string, sort_key?: ?string, sort_data_type?: ?string}>  $items
     * @return list<SequenceRow>
     */
    public function syncNatural(string $listId, array $items): array
    {
        return $this->engine->syncNatural($listId, $items);
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
    public function jump(string $listId, string $itemId, int $toSequence): array
    {
        return $this->engine->jump($listId, $itemId, $toSequence);
    }

    /**
     * @return list<SequenceRow>
     */
    public function reorder(string $listId, string $itemId, ?string $afterItemId): array
    {
        return $this->engine->reorder($listId, $itemId, $afterItemId);
    }

    /**
     * @return list<SequenceRow>
     */
    public function remove(string $listId, string $itemId): array
    {
        return $this->engine->remove($listId, $itemId);
    }

    /**
     * @param  list<SequenceRow>  $prev
     * @param  list<SequenceRow>  $next
     * @return list<SequenceRow>
     */
    public function diffSequences(array $prev, array $next): array
    {
        $prevMap = [];
        foreach ($prev as $row) {
            $prevMap[$row->id] = $row->sequence;
        }

        $changed = [];
        foreach ($next as $row) {
            if (! isset($prevMap[$row->id]) || $prevMap[$row->id] !== $row->sequence) {
                $changed[] = $row;
            }
        }

        return $changed;
    }
}
