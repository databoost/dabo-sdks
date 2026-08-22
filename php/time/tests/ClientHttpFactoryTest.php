<?php
// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.

declare(strict_types=1);

namespace Databoost\Time\Tests;

use Databoost\Time\Client;
use Databoost\Time\Engine;
use PHPUnit\Framework\TestCase;

final class ClientHttpFactoryTest extends TestCase
{
    public function test_http_factory_returns_client(): void
    {
        $client = Client::http('https://time.example.test', 'token', 'tenant');
        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(\Databoost\Time\HttpEngine::class, $client->engine());
    }

    public function test_client_delegates_timer_methods(): void
    {
        $engine = new class implements Engine {
            /** @var list<string> */
            public array $calls = [];

            public function listProjects(): array
            {
                $this->calls[] = 'listProjects';

                return [['id' => 1, 'name' => 'P']];
            }

            public function createProject(array $attrs): array
            {
                return $attrs;
            }

            public function getProject(int $id): array
            {
                return ['id' => $id];
            }

            public function updateProject(int $id, array $attrs): array
            {
                return ['id' => $id] + $attrs;
            }

            public function listTasks(): array
            {
                return [];
            }

            public function createTask(array $attrs): array
            {
                return $attrs;
            }

            public function getTask(int $id): array
            {
                return ['id' => $id];
            }

            public function updateTask(int $id, array $attrs): array
            {
                return ['id' => $id] + $attrs;
            }

            public function listTimeEntries(array $filters = []): array
            {
                return [];
            }

            public function createTimeEntry(array $attrs): array
            {
                return $attrs + ['is_running' => ! isset($attrs['hours'])];
            }

            public function getTimeEntry(int $id): array
            {
                return ['id' => $id];
            }

            public function updateTimeEntry(int $id, array $attrs): array
            {
                return ['id' => $id] + $attrs;
            }

            public function deleteTimeEntry(int $id): array
            {
                return ['id' => $id, 'deleted' => true];
            }

            public function stopTimer(int $id): array
            {
                $this->calls[] = 'stop:'.$id;

                return ['id' => $id, 'is_running' => false];
            }

            public function restartTimer(int $id): array
            {
                $this->calls[] = 'restart:'.$id;

                return ['id' => $id, 'is_running' => true];
            }
        };

        $client = new Client($engine);
        $this->assertSame([['id' => 1, 'name' => 'P']], $client->listProjects());
        $stopped = $client->stopTimer(9);
        $this->assertFalse($stopped['is_running']);
        $this->assertSame(['listProjects', 'stop:9'], $engine->calls);
        $entry = $client->createTimeEntry([
            'project_id' => 1,
            'task_id' => 2,
            'spent_date' => '2026-08-14',
            'redmine_issue_id' => '12345',
        ]);
        $this->assertTrue($entry['is_running']);
        $this->assertSame('12345', $entry['redmine_issue_id']);
    }
}
