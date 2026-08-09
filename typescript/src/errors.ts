/** Raised when the SRO HTTP API returns an error or an unexpected payload. */
export class SroError extends Error {
  override readonly name = "SroError";

  constructor(message: string, options?: ErrorOptions) {
    super(message, options);
  }
}
