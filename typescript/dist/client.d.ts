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
/**
 * Thin HTTP client for the SRO service.
 * Method names match OpenAPI / PHP (camelCase). All calls are async (fetch).
 */
export declare class Client {
    private readonly baseUrl;
    private readonly apiToken;
    private readonly tenantId;
    private readonly fetchImpl;
    constructor(options: ClientOptions);
    health(): Promise<{
        status: string;
    }>;
    syncNatural(listId: string, items: readonly SyncNaturalItem[], expectedVersion?: number | null): Promise<SequenceRow[]>;
    list(listId: string): Promise<SequenceRow[]>;
    jump(listId: string, itemId: string, toSequence: number, expectedVersion?: number | null): Promise<SequenceRow[]>;
    reorder(listId: string, itemId: string, afterItemId: string | null, beforeItemId?: string | null, expectedVersion?: number | null): Promise<SequenceRow[]>;
    remove(listId: string, itemId: string, expectedVersion?: number | null): Promise<SequenceRow[]>;
    resetSticky(listId: string, itemId: string, expectedVersion?: number | null): Promise<SequenceRow[]>;
    resetStickies(listId: string, expectedVersion?: number | null): Promise<SequenceRow[]>;
    private normalizeSyncItem;
    private listPath;
    private request;
    private json;
}
//# sourceMappingURL=client.d.ts.map