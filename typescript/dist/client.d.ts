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
    syncNatural(listId: string, items: readonly SyncNaturalItem[]): Promise<SequenceRow[]>;
    list(listId: string): Promise<SequenceRow[]>;
    jump(listId: string, itemId: string, toSequence: number): Promise<SequenceRow[]>;
    reorder(listId: string, itemId: string, afterItemId: string | null): Promise<SequenceRow[]>;
    remove(listId: string, itemId: string): Promise<SequenceRow[]>;
    resetSticky(listId: string, itemId: string): Promise<SequenceRow[]>;
    resetStickies(listId: string): Promise<SequenceRow[]>;
    private normalizeSyncItem;
    private listPath;
    private request;
}
//# sourceMappingURL=client.d.ts.map