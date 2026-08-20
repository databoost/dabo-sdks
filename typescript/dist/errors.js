/** Raised when the SRO HTTP API returns an error or an unexpected payload. */
export class SroError extends Error {
    name = "SroError";
    constructor(message, options) {
        super(message, options);
    }
}
//# sourceMappingURL=errors.js.map