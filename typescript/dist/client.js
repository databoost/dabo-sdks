// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.
import { SroError } from "./errors.js";
/**
 * Thin HTTP client for the SRO service.
 * Method names match OpenAPI / PHP (camelCase). All calls are async (fetch).
 */
export class Client {
    baseUrl;
    apiToken;
    tenantId;
    fetchImpl;
    constructor(options) {
        this.baseUrl = options.baseUrl.replace(/\/+$/, "");
        this.apiToken = options.apiToken;
        this.tenantId = options.tenantId;
        this.fetchImpl = options.fetch ?? fetch;
    }
    async health() {
        const data = await this.json("GET", "/health", null, { auth: false });
        return { status: String(data.status ?? "") };
    }
    async syncNatural(listId, items, expectedVersion) {
        const payload = {
            items: items.map((item) => this.normalizeSyncItem(item)),
        };
        if (expectedVersion != null)
            payload.expected_version = expectedVersion;
        return this.request("POST", this.listPath(listId, "syncNatural"), payload);
    }
    async list(listId) {
        return this.request("GET", this.listPath(listId), null);
    }
    async jump(listId, itemId, toSequence, expectedVersion) {
        const payload = {
            item_id: itemId,
            to_sequence: toSequence,
        };
        if (expectedVersion != null)
            payload.expected_version = expectedVersion;
        return this.request("POST", this.listPath(listId, "jump"), payload);
    }
    async reorder(listId, itemId, afterItemId, beforeItemId, expectedVersion) {
        const payload = { item_id: itemId };
        if (beforeItemId != null)
            payload.before_item_id = beforeItemId;
        else
            payload.after_item_id = afterItemId;
        if (expectedVersion != null)
            payload.expected_version = expectedVersion;
        return this.request("POST", this.listPath(listId, "reorder"), payload);
    }
    async remove(listId, itemId, expectedVersion) {
        const payload = { item_id: itemId };
        if (expectedVersion != null)
            payload.expected_version = expectedVersion;
        return this.request("POST", this.listPath(listId, "remove"), payload);
    }
    async resetSticky(listId, itemId, expectedVersion) {
        const payload = { item_id: itemId };
        if (expectedVersion != null)
            payload.expected_version = expectedVersion;
        return this.request("POST", this.listPath(listId, "resetSticky"), payload);
    }
    async resetStickies(listId, expectedVersion) {
        const payload = expectedVersion == null ? null : { expected_version: expectedVersion };
        return this.request("POST", this.listPath(listId, "resetStickies"), payload);
    }
    normalizeSyncItem(item) {
        if (item.id === undefined || item.id === null || item.id === "") {
            throw new SroError("syncNatural items must include id");
        }
        return {
            id: String(item.id),
            sort_key: item.sort_key ?? null,
            sort_data_type: item.sort_data_type ?? null,
        };
    }
    listPath(listId, action) {
        const tenant = encodeURIComponent(this.tenantId);
        const list = encodeURIComponent(listId);
        const path = `/v1/tenants/${tenant}/lists/${list}`;
        return action ? `${path}/${action}` : path;
    }
    async request(method, path, body) {
        const data = await this.json(method, path, body, { auth: true });
        const items = data.items;
        if (!Array.isArray(items)) {
            throw new SroError("SRO HTTP response missing items");
        }
        const rows = [];
        for (const row of items) {
            if (typeof row !== "object" || row === null)
                continue;
            const r = row;
            if (r.id === undefined || r.sequence === undefined)
                continue;
            rows.push({
                id: String(r.id),
                sequence: Number(r.sequence),
                sticky: r.sticky === true,
            });
        }
        return rows;
    }
    async json(method, path, body, opts) {
        const headers = {
            Accept: "application/json",
        };
        if (opts.auth) {
            headers.Authorization = `Bearer ${this.apiToken}`;
            headers["X-Tenant-Id"] = this.tenantId;
        }
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
            const message = errorMessage(data) ?? `SRO HTTP ${res.status}`;
            throw new SroError(message);
        }
        if (data.error && typeof data.error === "object") {
            throw new SroError(errorMessage(data) ?? "SRO HTTP error");
        }
        return data;
    }
}
function errorMessage(data) {
    const err = data.error;
    if (typeof err !== "object" || err === null)
        return undefined;
    const message = err.message;
    return typeof message === "string" ? message : undefined;
}
//# sourceMappingURL=client.js.map