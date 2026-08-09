import { describe, expect, it, vi } from "vitest";
import { Client, SroError } from "../src/index.js";

function jsonResponse(body: unknown, status = 200): Response {
  return new Response(JSON.stringify(body), {
    status,
    headers: { "Content-Type": "application/json" },
  });
}

function client(fetchImpl: typeof fetch): Client {
  return new Client({
    baseUrl: "https://sro.example.test",
    apiToken: "token",
    tenantId: "demo",
    fetch: fetchImpl,
  });
}

describe("Client", () => {
  it("list parses sequence rows", async () => {
    const fetchImpl = vi.fn(async (input: RequestInfo | URL, init?: RequestInit) => {
      expect(String(input)).toBe(
        "https://sro.example.test/v1/tenants/demo/lists/bindery",
      );
      expect(init?.method).toBe("GET");
      expect(init?.headers).toMatchObject({
        Authorization: "Bearer token",
        "X-Tenant-Id": "demo",
      });
      return jsonResponse({
        items: [
          { id: "a", sequence: 1 },
          { id: "b", sequence: 2 },
        ],
        version: 3,
      });
    }) as unknown as typeof fetch;

    const rows = await client(fetchImpl).list("bindery");
    expect(rows).toEqual([
      { id: "a", sequence: 1 },
      { id: "b", sequence: 2 },
    ]);
  });

  it("syncNatural posts items", async () => {
    const fetchImpl = vi.fn(async (input: RequestInfo | URL, init?: RequestInit) => {
      expect(String(input)).toMatch(/\/syncNatural$/);
      expect(init?.method).toBe("POST");
      expect(JSON.parse(String(init?.body))).toEqual({
        items: [
          { id: "a", sort_key: "2026-08-01", sort_data_type: "date" },
        ],
      });
      return jsonResponse({ items: [{ id: "a", sequence: 1 }], version: 1 });
    }) as unknown as typeof fetch;

    const rows = await client(fetchImpl).syncNatural("bindery", [
      { id: "a", sort_key: "2026-08-01", sort_data_type: "date" },
    ]);
    expect(rows).toEqual([{ id: "a", sequence: 1 }]);
  });

  it("resetSticky and resetStickies hit the right paths", async () => {
    const calls: { url: string; body: string | null | undefined }[] = [];
    const fetchImpl = vi.fn(async (input: RequestInfo | URL, init?: RequestInit) => {
      calls.push({
        url: String(input),
        body: init?.body === undefined ? undefined : String(init.body),
      });
      return jsonResponse({ items: [], version: 1 });
    }) as unknown as typeof fetch;

    const c = client(fetchImpl);
    await c.resetSticky("bindery", "job-1");
    await c.resetStickies("bindery");

    expect(calls[0]?.url).toMatch(/\/resetSticky$/);
    expect(JSON.parse(calls[0]?.body ?? "")).toEqual({ item_id: "job-1" });
    expect(calls[1]?.url).toMatch(/\/resetStickies$/);
    expect(calls[1]?.body).toBeUndefined();
  });

  it("maps HTTP error message", async () => {
    const fetchImpl = vi.fn(async () =>
      jsonResponse(
        { error: { code: "not_found", message: "List missing" } },
        404,
      ),
    ) as unknown as typeof fetch;

    await expect(client(fetchImpl).remove("bindery", "a")).rejects.toThrow(
      SroError,
    );
    await expect(client(fetchImpl).remove("bindery", "a")).rejects.toThrow(
      "List missing",
    );
  });
});
