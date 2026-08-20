// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.
import { SroError } from "./errors.js";

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
export class AdminClient {
  private readonly baseUrl: string;
  private readonly adminToken: string;
  private readonly fetchImpl: typeof fetch;

  constructor(options: AdminClientOptions) {
    this.baseUrl = options.baseUrl.replace(/\/+$/, "");
    this.adminToken = options.adminToken;
    this.fetchImpl = options.fetch ?? fetch;
  }

  async health(): Promise<{ status: string }> {
    const data = await this.json("GET", "/health", null, false);
    return { status: String(data.status ?? "") };
  }

  async listTenants(): Promise<JsonObject> {
    return this.json("GET", "/admin/v1/tenants", null, true);
  }

  async reconcileTenants(
    tenantIds: readonly string[],
    opts?: { dryRun?: boolean; allowEmpty?: boolean },
  ): Promise<JsonObject> {
    return this.json(
      "POST",
      "/admin/v1/tenants/reconcile",
      {
        tenant_ids: [...tenantIds],
        dry_run: opts?.dryRun ?? false,
        allow_empty: opts?.allowEmpty ?? false,
      },
      true,
    );
  }

  async provisionTenant(tenantId: string, body: JsonObject): Promise<JsonObject> {
    return this.json(
      "PUT",
      `/admin/v1/tenants/${encodeURIComponent(tenantId)}`,
      body,
      true,
    );
  }

  async updateTenant(tenantId: string, body: JsonObject): Promise<JsonObject> {
    return this.json(
      "PATCH",
      `/admin/v1/tenants/${encodeURIComponent(tenantId)}`,
      body,
      true,
    );
  }

  async deleteTenant(tenantId: string): Promise<JsonObject> {
    return this.json(
      "DELETE",
      `/admin/v1/tenants/${encodeURIComponent(tenantId)}`,
      null,
      true,
    );
  }

  async regenerateToken(
    tenantId: string,
    tokenLabel?: string | null,
  ): Promise<JsonObject> {
    const body = tokenLabel == null ? null : { token_label: tokenLabel };
    return this.json(
      "POST",
      `/admin/v1/tenants/${encodeURIComponent(tenantId)}/token`,
      body,
      true,
    );
  }

  async revokeToken(tenantId: string): Promise<JsonObject> {
    return this.json(
      "DELETE",
      `/admin/v1/tenants/${encodeURIComponent(tenantId)}/token`,
      null,
      true,
    );
  }

  private async json(
    method: string,
    path: string,
    body: JsonObject | null,
    auth: boolean,
  ): Promise<JsonObject> {
    const headers: Record<string, string> = { Accept: "application/json" };
    if (auth) headers.Authorization = `Bearer ${this.adminToken}`;
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
      const err = data.error;
      const message =
        typeof err === "object" && err !== null && typeof (err as JsonObject).message === "string"
          ? String((err as JsonObject).message)
          : `SRO HTTP ${res.status}`;
      throw new SroError(message);
    }
    return data;
  }
}
