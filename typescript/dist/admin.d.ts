export type AdminClientOptions = {
    baseUrl: string;
    adminToken: string;
    fetch?: typeof fetch;
};
type JsonObject = Record<string, unknown>;
/**
 * SRO admin HTTP client (SRO_ADMIN_TOKEN). Do not send X-Tenant-Id.
 * Token plaintext is returned once on provision/regenerate — never log it.
 */
export declare class AdminClient {
    private readonly baseUrl;
    private readonly adminToken;
    private readonly fetchImpl;
    constructor(options: AdminClientOptions);
    health(): Promise<{
        status: string;
    }>;
    listTenants(): Promise<JsonObject>;
    reconcileTenants(tenantIds: readonly string[], opts?: {
        dryRun?: boolean;
        allowEmpty?: boolean;
    }): Promise<JsonObject>;
    provisionTenant(tenantId: string, body: JsonObject): Promise<JsonObject>;
    updateTenant(tenantId: string, body: JsonObject): Promise<JsonObject>;
    deleteTenant(tenantId: string): Promise<JsonObject>;
    regenerateToken(tenantId: string, tokenLabel?: string | null): Promise<JsonObject>;
    revokeToken(tenantId: string): Promise<JsonObject>;
    private json;
}
export {};
//# sourceMappingURL=admin.d.ts.map