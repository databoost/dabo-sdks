// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.
import { SroError } from "./errors.js";
/**
 * SRO admin HTTP client (SRO_ADMIN_TOKEN). Do not send X-Tenant-Id.
 * Token plaintext is returned once on provision/regenerate — never log it.
 */
export class AdminClient {
    baseUrl;
    adminToken;
    fetchImpl;
    constructor(options) {
        this.baseUrl = options.baseUrl.replace(/\/+$/, "");
        this.adminToken = options.adminToken;
        this.fetchImpl = options.fetch ?? fetch;
    }
    async health() {
        const data = await this.json("GET", "/health", null, false);
        return { status: String(data.status ?? "") };
    }
    async listTenants() {
        return this.json("GET", "/admin/v1/tenants", null, true);
    }
    async reconcileTenants(tenantIds, opts) {
        return this.json("POST", "/admin/v1/tenants/reconcile", {
            tenant_ids: [...tenantIds],
            dry_run: opts?.dryRun ?? false,
            allow_empty: opts?.allowEmpty ?? false,
        }, true);
    }
    async provisionTenant(tenantId, body) {
        return this.json("PUT", `/admin/v1/tenants/${encodeURIComponent(tenantId)}`, body, true);
    }
    async updateTenant(tenantId, body) {
        return this.json("PATCH", `/admin/v1/tenants/${encodeURIComponent(tenantId)}`, body, true);
    }
    async deleteTenant(tenantId) {
        return this.json("DELETE", `/admin/v1/tenants/${encodeURIComponent(tenantId)}`, null, true);
    }
    async regenerateToken(tenantId, tokenLabel) {
        const body = tokenLabel == null ? null : { token_label: tokenLabel };
        return this.json("POST", `/admin/v1/tenants/${encodeURIComponent(tenantId)}/token`, body, true);
    }
    async revokeToken(tenantId) {
        return this.json("DELETE", `/admin/v1/tenants/${encodeURIComponent(tenantId)}/token`, null, true);
    }
    async json(method, path, body, auth) {
        const headers = { Accept: "application/json" };
        if (auth)
            headers.Authorization = `Bearer ${this.adminToken}`;
        const init = { method, headers };
        if (body !== null) {
            headers["Content-Type"] = "application/json";
            init.body = JSON.stringify(body);
        }
        let res;
        try {
            res = await this.fetchImpl(`${this.baseUrl}${path}`, init);
        }
        catch (err) {
            throw new SroError("SRO HTTP request failed", { cause: err });
        }
        const raw = await res.text();
        let data;
        try {
            const parsed = raw === "" ? {} : JSON.parse(raw);
            if (typeof parsed !== "object" || parsed === null || Array.isArray(parsed)) {
                throw new SroError("SRO HTTP response was not a JSON object");
            }
            data = parsed;
        }
        catch (err) {
            if (err instanceof SroError)
                throw err;
            throw new SroError("SRO HTTP response was not JSON", { cause: err });
        }
        if (!res.ok) {
            const err = data.error;
            const message = typeof err === "object" && err !== null && typeof err.message === "string"
                ? String(err.message)
                : `SRO HTTP ${res.status}`;
            throw new SroError(message);
        }
        return data;
    }
}
//# sourceMappingURL=admin.js.map