// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.
import { SroError } from "./errors.js";

/** Dense production sequence row (1…n) plus sticky flag. Never ranking keys. */
export type SequenceRow = {
  id: string;
  sequence: number;
  sticky: boolean;
};

export type SyncNaturalItem = {
  id: string;
  sort_key?: string | null;
  sort_data_type?: string | null;
};

export type ClientOptions = {
  baseUrl: string;
  apiToken: string;
  tenantId: string;
  /** Inject for tests; defaults to global fetch. */
  fetch?: typeof fetch;
};

type JsonObject = Record<string, unknown>;

/**
 * Thin HTTP client for the SRO service.
 * Method names match OpenAPI / PHP (camelCase). All calls are async (fetch).
 */
export class Client {
  private readonly baseUrl: string;
  private readonly apiToken: string;
  private readonly tenantId: string;
  private readonly fetchImpl: typeof fetch;

  constructor(options: ClientOptions) {
    this.baseUrl = options.baseUrl.replace(/\/+$/, "");
    this.apiToken = options.apiToken;
    this.tenantId = options.tenantId;
    this.fetchImpl = options.fetch ?? fetch;
  }

  async syncNatural(
    listId: string,
    items: readonly SyncNaturalItem[],
  ): Promise<SequenceRow[]> {
    const payload = {
      items: items.map((item) => this.normalizeSyncItem(item)),
    };
    return this.request("POST", this.listPath(listId, "syncNatural"), payload);
  }

  async list(listId: string): Promise<SequenceRow[]> {
    return this.request("GET", this.listPath(listId), null);
  }

  async jump(
    listId: string,
    itemId: string,
    toSequence: number,
  ): Promise<SequenceRow[]> {
    return this.request("POST", this.listPath(listId, "jump"), {
      item_id: itemId,
      to_sequence: toSequence,
    });
  }

  async reorder(
    listId: string,
    itemId: string,
    afterItemId: string | null,
  ): Promise<SequenceRow[]> {
    return this.request("POST", this.listPath(listId, "reorder"), {
      item_id: itemId,
      after_item_id: afterItemId,
    });
  }

  async remove(listId: string, itemId: string): Promise<SequenceRow[]> {
    return this.request("POST", this.listPath(listId, "remove"), {
      item_id: itemId,
    });
  }

  async resetSticky(listId: string, itemId: string): Promise<SequenceRow[]> {
    return this.request("POST", this.listPath(listId, "resetSticky"), {
      item_id: itemId,
    });
  }

  async resetStickies(listId: string): Promise<SequenceRow[]> {
    return this.request("POST", this.listPath(listId, "resetStickies"), null);
  }

  private normalizeSyncItem(item: SyncNaturalItem): SyncNaturalItem {
    if (item.id === undefined || item.id === null || item.id === "") {
      throw new SroError("syncNatural items must include id");
    }
    return {
      id: String(item.id),
      sort_key: item.sort_key ?? null,
      sort_data_type: item.sort_data_type ?? null,
    };
  }

  private listPath(listId: string, action?: string): string {
    const tenant = encodeURIComponent(this.tenantId);
    const list = encodeURIComponent(listId);
    const path = `/v1/tenants/${tenant}/lists/${list}`;
    return action ? `${path}/${action}` : path;
  }

  private async request(
    method: string,
    path: string,
    body: JsonObject | null,
  ): Promise<SequenceRow[]> {
    const headers: Record<string, string> = {
      Authorization: `Bearer ${this.apiToken}`,
      "X-Tenant-Id": this.tenantId,
      Accept: "application/json",
    };
    const init: RequestInit = { method, headers };
    if (body !== null) {
      headers["Content-Type"] = "application/json";
      init.body = JSON.stringify(body);
    }

    let res: Response;
    try {
      res = await this.fetchImpl(`${this.baseUrl}${path}`, init);
    } catch (err) {
      throw new SroError("SRO HTTP request failed", { cause: err });
    }

    const raw = await res.text();
    let data: JsonObject;
    try {
      const parsed: unknown = raw === "" ? {} : JSON.parse(raw);
      if (typeof parsed !== "object" || parsed === null || Array.isArray(parsed)) {
        throw new SroError("SRO HTTP response was not a JSON object");
      }
      data = parsed as JsonObject;
    } catch (err) {
      if (err instanceof SroError) throw err;
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

    const rows: SequenceRow[] = [];
    for (const row of items) {
      if (typeof row !== "object" || row === null) continue;
      const r = row as JsonObject;
      if (r.id === undefined || r.sequence === undefined) continue;
      rows.push({
        id: String(r.id),
        sequence: Number(r.sequence),
        sticky: r.sticky === true,
      });
    }
    return rows;
  }
}

function errorMessage(data: JsonObject): string | undefined {
  const err = data.error;
  if (typeof err !== "object" || err === null) return undefined;
  const message = (err as JsonObject).message;
  return typeof message === "string" ? message : undefined;
}
