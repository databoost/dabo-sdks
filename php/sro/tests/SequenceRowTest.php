<?php
// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.

declare(strict_types=1);

namespace Databoost\Sro\Tests;

use Databoost\Sro\SequenceRow;
use PHPUnit\Framework\TestCase;

final class SequenceRowTest extends TestCase
{
    public function test_from_api_maps_sticky_true(): void
    {
        $row = SequenceRow::fromApi([
            'id' => 'job-1',
            'sequence' => 3,
            'sticky' => true,
        ]);

        $this->assertInstanceOf(SequenceRow::class, $row);
        $this->assertSame('job-1', $row->id);
        $this->assertSame(3, $row->sequence);
        $this->assertTrue($row->sticky);
    }

    public function test_from_api_defaults_missing_sticky_to_false(): void
    {
        $row = SequenceRow::fromApi(['id' => 'a', 'sequence' => 1]);

        $this->assertInstanceOf(SequenceRow::class, $row);
        $this->assertFalse($row->sticky);
    }

    public function test_from_api_ignores_major_minor(): void
    {
        $row = SequenceRow::fromApi([
            'id' => 'b',
            'sequence' => 2,
            'sticky' => false,
            'major_minor' => '5.1',
        ]);

        $this->assertInstanceOf(SequenceRow::class, $row);
        $this->assertFalse($row->sticky);
        $this->assertObjectNotHasProperty('major_minor', $row);
    }

    public function test_from_api_rejects_incomplete_rows(): void
    {
        $this->assertNull(SequenceRow::fromApi(['id' => 'a']));
        $this->assertNull(SequenceRow::fromApi(['sequence' => 1]));
    }
}
