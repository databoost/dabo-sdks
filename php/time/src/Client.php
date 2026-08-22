<?php
// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.

declare(strict_types=1);

namespace Databoost\Time;

/**
 * Façade for the dabo-time HTTP API (entries/timers live on the service).
 *
 * @example
 * $client = Client::http($baseUrl, $token, 'lpp-dev');
 * $project = $client->createProject(['name' => 'Bindery']);
 * $task = $client->createTask(['name' => 'Make ready']);
 * $running = $client->createTimeEntry([
 *     'project_id' => $project['id'],
 *     'task_id' => $task['id'],
 *     'spent_date' => '2026-08-14',
 *     'redmine_issue_id' => '12345',
 * ]);
 * $stopped = $client->stopTimer((int) $running['id']);
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
     * @return list<array<string, mixed>>
     */
    public function listProjects(): array
    {
        return $this->engine->listProjects();
    }

    /**
     * @param  array<string, mixed>  $attrs
     * @return array<string, mixed>
     */
    public function createProject(array $attrs): array
    {
        return $this->engine->createProject($attrs);
    }

    /**
     * @return array<string, mixed>
     */
    public function getProject(int $id): array
    {
        return $this->engine->getProject($id);
    }

    /**
     * @param  array<string, mixed>  $attrs
     * @return array<string, mixed>
     */
    public function updateProject(int $id, array $attrs): array
    {
        return $this->engine->updateProject($id, $attrs);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listTasks(): array
    {
        return $this->engine->listTasks();
    }

    /**
     * @param  array<string, mixed>  $attrs
     * @return array<string, mixed>
     */
    public function createTask(array $attrs): array
    {
        return $this->engine->createTask($attrs);
    }

    /**
     * @return array<string, mixed>
     */
    public function getTask(int $id): array
    {
        return $this->engine->getTask($id);
    }

    /**
     * @param  array<string, mixed>  $attrs
     * @return array<string, mixed>
     */
    public function updateTask(int $id, array $attrs): array
    {
        return $this->engine->updateTask($id, $attrs);
    }

    /**
     * @param  array<string, string>  $filters
     * @return list<array<string, mixed>>
     */
    public function listTimeEntries(array $filters = []): array
    {
        return $this->engine->listTimeEntries($filters);
    }

    /**
     * @param  array<string, mixed>  $attrs
     * @return array<string, mixed>
     */
    public function createTimeEntry(array $attrs): array
    {
        return $this->engine->createTimeEntry($attrs);
    }

    /**
     * @return array<string, mixed>
     */
    public function getTimeEntry(int $id): array
    {
        return $this->engine->getTimeEntry($id);
    }

    /**
     * @param  array<string, mixed>  $attrs
     * @return array<string, mixed>
     */
    public function updateTimeEntry(int $id, array $attrs): array
    {
        return $this->engine->updateTimeEntry($id, $attrs);
    }

    /**
     * @return array<string, mixed>
     */
    public function deleteTimeEntry(int $id): array
    {
        return $this->engine->deleteTimeEntry($id);
    }

    /**
     * @return array<string, mixed>
     */
    public function stopTimer(int $id): array
    {
        return $this->engine->stopTimer($id);
    }

    /**
     * @return array<string, mixed>
     */
    public function restartTimer(int $id): array
    {
        return $this->engine->restartTimer($id);
    }
}
