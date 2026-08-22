<?php
// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.

declare(strict_types=1);

namespace Databoost\Time;

/**
 * Thin HTTP surface for dabo-time (service owns entries/timers).
 */
interface Engine
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listProjects(): array;

    /**
     * @param  array<string, mixed>  $attrs
     * @return array<string, mixed>
     */
    public function createProject(array $attrs): array;

    /**
     * @return array<string, mixed>
     */
    public function getProject(int $id): array;

    /**
     * @param  array<string, mixed>  $attrs
     * @return array<string, mixed>
     */
    public function updateProject(int $id, array $attrs): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function listTasks(): array;

    /**
     * @param  array<string, mixed>  $attrs
     * @return array<string, mixed>
     */
    public function createTask(array $attrs): array;

    /**
     * @return array<string, mixed>
     */
    public function getTask(int $id): array;

    /**
     * @param  array<string, mixed>  $attrs
     * @return array<string, mixed>
     */
    public function updateTask(int $id, array $attrs): array;

    /**
     * @param  array<string, string>  $filters  from, to, project_id, is_running
     * @return list<array<string, mixed>>
     */
    public function listTimeEntries(array $filters = []): array;

    /**
     * Omit hours to start a running timer.
     *
     * @param  array<string, mixed>  $attrs
     * @return array<string, mixed>
     */
    public function createTimeEntry(array $attrs): array;

    /**
     * @return array<string, mixed>
     */
    public function getTimeEntry(int $id): array;

    /**
     * @param  array<string, mixed>  $attrs
     * @return array<string, mixed>
     */
    public function updateTimeEntry(int $id, array $attrs): array;

    /**
     * @return array<string, mixed>
     */
    public function deleteTimeEntry(int $id): array;

    /**
     * @return array<string, mixed>
     */
    public function stopTimer(int $id): array;

    /**
     * @return array<string, mixed>
     */
    public function restartTimer(int $id): array;
}
