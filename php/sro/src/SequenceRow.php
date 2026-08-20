<?php
// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.

declare(strict_types=1);

namespace Databoost\Sro;

/**
 * Client-visible row: opaque id, dense production sequence 1…n, sticky flag.
 * Never major.minor / ranking keys.
 */
final class SequenceRow
{
    public function __construct(
        public readonly string $id,
        public readonly int $sequence,
        public readonly bool $sticky = false,
    ) {}

    /**
     * @param  array<string, mixed>  $row
     */
    public static function fromApi(array $row): ?self
    {
        if (! isset($row['id'], $row['sequence'])) {
            return null;
        }

        return new self(
            (string) $row['id'],
            (int) $row['sequence'],
            (bool) ($row['sticky'] ?? false),
        );
    }
}
