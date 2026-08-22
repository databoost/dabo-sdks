<?php
// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.

declare(strict_types=1);

namespace Databoost\SmoothCurve;

/**
 * Façade for the SmoothCurve HTTP API (easing math lives on the service).
 *
 * @example
 * $client = Client::http($baseUrl, $token, 'lpp-dev');
 * $curve = $client->curve(['a' => 0, 'b' => 100, 'c' => 50]);
 * $mapped = $client->map([
 *     'a' => 0, 'b' => 100, 'c' => 50, 'g' => 0, 'h' => 1,
 *     'ease_mode' => 'linear', 'ease_exponent' => 2, 'clamp' => true,
 * ]);
 */
final class Client
{
    public function __construct(
        private Engine $engine,
    ) {}

    /**
     * Build a Client over HttpEngine. $baseUrl is required (no default).
     */
    public static function http(string $baseUrl, string $apiToken, string $tenantId): self
    {
        return new self(new HttpEngine($baseUrl, $apiToken, $tenantId));
    }

    public function engine(): Engine
    {
        return $this->engine;
    }

    /**
     * @param  array{a: float|int, b: float|int, c: float|int, ease_mode?: string, ease_exponent?: float|int, clamp?: bool}  $body
     * @return array{t: float|int}
     */
    public function curve(array $body): array
    {
        return $this->engine->curve($body);
    }

    /**
     * @param  array{a: float|int, b: float|int, c: float|int, g: float|int, h: float|int, ease_mode?: string, ease_exponent?: float|int, clamp?: bool}  $body
     * @return array{value: float|int}
     */
    public function map(array $body): array
    {
        return $this->engine->map($body);
    }
}
