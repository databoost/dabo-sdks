<?php

declare(strict_types=1);

namespace Databoost\Sro;

/**
 * Client-visible row: opaque id + dense production sequence 1…n (never major.minor).
 */
final class SequenceRow
{
    public function __construct(
        public readonly string $id,
        public readonly int $sequence,
    ) {}
}
