<?php

declare(strict_types=1);

namespace Databoost\Sro;

/**
 * HTTP ranking surface (service owns item state). Thin client contract only.
 */
interface Engine
{
    /**
     * @param  list<array{id: string, sort_key?: ?string, sort_data_type?: ?string}>  $items
     * @return list<SequenceRow>
     */
    public function syncNatural(string $listId, array $items): array;

    /**
     * @return list<SequenceRow>
     */
    public function list(string $listId): array;

    /**
     * @return list<SequenceRow>
     */
    public function jump(string $listId, string $itemId, int $toSequence): array;

    /**
     * Place $itemId after $afterItemId (null = first).
     *
     * @return list<SequenceRow>
     */
    public function reorder(string $listId, string $itemId, ?string $afterItemId): array;

    /**
     * @return list<SequenceRow>
     */
    public function remove(string $listId, string $itemId): array;

    /**
     * Clear sticky major/minor on one item, then densify naturals.
     *
     * @return list<SequenceRow>
     */
    public function resetSticky(string $listId, string $itemId): array;

    /**
     * Clear sticky major/minor on every row, then densify naturals.
     *
     * @return list<SequenceRow>
     */
    public function resetStickies(string $listId): array;
}
