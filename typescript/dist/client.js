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
    async syncNatural(listId, items) {
        const payload = {
            items: items.map((item) => this.normalizeSyncItem(item)),
        };
        return this.request("POST", this.listPath(listId, "syncNatural"), payload);
    }
    async list(listId) {
        return this.request("GET", this.listPath(listId), null);
    }
    async jump(listId, itemId, toSequence) {
        return this.request("POST", this.listPath(listId, "jump"), {
            item_id: itemId,
            to_sequence: toSequence,
        });
    }
    async reorder(listId, itemId, afterItemId) {
        return this.request("POST", this.listPath(listId, "reorder"), {
            item_id: itemId,
            after_item_id: afterItemId,
        });
    }
    async remove(listId, itemId) {
        return this.request("POST", this.listPath(listId, "remove"), {
            item_id: itemId,
        });
    }
    async resetSticky(listId, itemId) {
        return this.request("POST", this.listPath(listId, "resetSticky"), {
            item_id: itemId,
        });
    }
    async resetStickies(listId) {
        return this.request("POST", this.listPath(listId, "resetStickies"), null);
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
        const headers = {
            Authorization: `Bearer ${this.apiToken}`,
            "X-Tenant-Id": this.tenantId,
            Accept: "application/json",
        };
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
            rows.push({ id: String(r.id), sequence: Number(r.sequence) });
        }
        return rows;
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