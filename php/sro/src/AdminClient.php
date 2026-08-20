<?php
// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.

declare(strict_types=1);

namespace Databoost\Sro;

/**
 * SRO admin HTTP client (SRO_ADMIN_TOKEN). No X-Tenant-Id.
 *
 * Token plaintext is returned once on provision/regenerate — never log it.
 */
final class AdminClient
{
    public function __construct(
        private string $baseUrl,
        private string $adminToken,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public static function http(string $baseUrl, string $adminToken): self
    {
        return new self($baseUrl, $adminToken);
    }

    /**
     * @return array{status: string}
     */
    public function health(): array
    {
        $data = Transport::json('GET', $this->baseUrl.'/health', ['Accept: application/json'], null);

        return ['status' => (string) ($data['status'] ?? '')];
    }

    /**
     * @return array{tenants: list<array<string, mixed>>}
     */
    public function listTenants(): array
    {
        return $this->request('GET', '/admin/v1/tenants', null);
    }

    /**
     * @param  list<string>  $tenantIds
     * @return array<string, mixed>
     */
    public function reconcileTenants(array $tenantIds, bool $dryRun = false, bool $allowEmpty = false): array
    {
        return $this->request('POST', '/admin/v1/tenants/reconcile', [
            'tenant_ids' => $tenantIds,
            'dry_run' => $dryRun,
            'allow_empty' => $allowEmpty,
        ]);
    }

    /**
     * @param  array{org_id: string, env: string, name: string, status?: string, token_label?: ?string}  $body
     * @return array<string, mixed>
     */
    public function provisionTenant(string $tenantId, array $body): array
    {
        return $this->request('PUT', '/admin/v1/tenants/'.rawurlencode($tenantId), $body);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function updateTenant(string $tenantId, array $body): array
    {
        return $this->request('PATCH', '/admin/v1/tenants/'.rawurlencode($tenantId), $body);
    }

    /**
     * @return array<string, mixed>
     */
    public function deleteTenant(string $tenantId): array
    {
        return $this->request('DELETE', '/admin/v1/tenants/'.rawurlencode($tenantId), null);
    }

    /**
     * @return array<string, mixed>
     */
    public function regenerateToken(string $tenantId, ?string $tokenLabel = null): array
    {
        $body = $tokenLabel === null ? null : ['token_label' => $tokenLabel];

        return $this->request('POST', '/admin/v1/tenants/'.rawurlencode($tenantId).'/token', $body);
    }

    /**
     * @return array{tenant_id: string, revoked: int}
     */
    public function revokeToken(string $tenantId): array
    {
        return $this->request('DELETE', '/admin/v1/tenants/'.rawurlencode($tenantId).'/token', null);
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, ?array $body): array
    {
        $headers = [
            'Authorization: Bearer '.$this->adminToken,
            'Accept: application/json',
        ];
        $payload = null;
        if ($body !== null) {
            $payload = json_encode($body, JSON_THROW_ON_ERROR);
            $headers[] = 'Content-Type: application/json';
        }

        return Transport::json($method, $this->baseUrl.$path, $headers, $payload);
    }
}
