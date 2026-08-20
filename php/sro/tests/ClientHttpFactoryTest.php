<?php
// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.

declare(strict_types=1);

namespace Databoost\Sro\Tests;

use Databoost\Sro\AdminClient;
use Databoost\Sro\Client;
use Databoost\Sro\Engine;
use Databoost\Sro\HttpEngine;
use Databoost\Sro\SequenceRow;
use PHPUnit\Framework\TestCase;

final class ClientHttpFactoryTest extends TestCase
{
    public function test_http_factory_returns_client(): void
    {
        $client = Client::http('https://sro.databoost.com', 'token', 'tenant');
        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(HttpEngine::class, $client->engine());
    }

    public function test_http_admin_factory_returns_client(): void
    {
        $admin = AdminClient::http('https://sro.databoost.com', 'admin-token');
        $this->assertInstanceOf(AdminClient::class, $admin);
    }

    public function test_client_delegates_health(): void
    {
        $engine = new RecordingEngine();
        $client = new Client($engine);

        $this->assertSame(['status' => 'ok'], $client->health());
        $this->assertSame(['health'], $engine->calls);
    }

    public function test_client_delegates_reset_verbs(): void
    {
        $engine = new RecordingEngine();
        $client = new Client($engine);

        $one = $client->resetSticky('bindery', 'job-1');
        $all = $client->resetStickies('bindery');

        $this->assertSame(['resetSticky', 'resetStickies'], $engine->calls);
        $this->assertSame(['bindery', 'job-1'], $engine->resetStickyArgs);
        $this->assertSame(['bindery'], $engine->resetStickiesArgs);
        $this->assertSame('job-1', $one[0]->id);
        $this->assertFalse($one[0]->sticky);
        $this->assertSame([], $all);
    }

    public function test_diff_sequences_includes_sticky_flips(): void
    {
        $client = new Client(new RecordingEngine());
        $prev = [new SequenceRow('a', 1, true), new SequenceRow('b', 2, false)];
        $next = [new SequenceRow('a', 1, false), new SequenceRow('b', 2, false)];

        $changed = $client->diffSequences($prev, $next);

        $this->assertCount(1, $changed);
        $this->assertSame('a', $changed[0]->id);
        $this->assertFalse($changed[0]->sticky);
    }
}

final class RecordingEngine implements Engine
{
    /** @var list<string> */
    public array $calls = [];

    /** @var list<string> */
    public array $resetStickyArgs = [];

    /** @var list<string> */
    public array $resetStickiesArgs = [];

    public function health(): array
    {
        $this->calls[] = 'health';

        return ['status' => 'ok'];
    }

    public function syncNatural(string $listId, array $items, ?int $expectedVersion = null): array
    {
        $this->calls[] = 'syncNatural';

        return [];
    }

    public function list(string $listId): array
    {
        $this->calls[] = 'list';

        return [];
    }

    public function jump(string $listId, string $itemId, int $toSequence, ?int $expectedVersion = null): array
    {
        $this->calls[] = 'jump';

        return [];
    }

    public function reorder(
        string $listId,
        string $itemId,
        ?string $afterItemId,
        ?string $beforeItemId = null,
        ?int $expectedVersion = null,
    ): array {
        $this->calls[] = 'reorder';

        return [];
    }

    public function remove(string $listId, string $itemId, ?int $expectedVersion = null): array
    {
        $this->calls[] = 'remove';

        return [];
    }

    public function resetSticky(string $listId, string $itemId, ?int $expectedVersion = null): array
    {
        $this->calls[] = 'resetSticky';
        $this->resetStickyArgs = [$listId, $itemId];

        return [new SequenceRow($itemId, 1, false)];
    }

    public function resetStickies(string $listId, ?int $expectedVersion = null): array
    {
        $this->calls[] = 'resetStickies';
        $this->resetStickiesArgs = [$listId];

        return [];
    }
}
