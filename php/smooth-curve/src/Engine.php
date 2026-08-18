<?php
// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.

declare(strict_types=1);

namespace Databoost\SmoothCurve;

/**
 * Thin HTTP surface for SmoothCurve (service owns curve/map math).
 */
interface Engine
{
    /**
     * POST /v1/tenants/{tenant}/curve — eased t on [0, 1].
     *
     * @param  array{a: float|int, b: float|int, c: float|int, ease_mode?: string, ease_exponent?: float|int, clamp?: bool}  $body
     * @return array{t: float|int}
     */
    public function curve(array $body): array;

    /**
     * POST /v1/tenants/{tenant}/map — affine map of eased t onto [g, h].
     *
     * @param  array{a: float|int, b: float|int, c: float|int, g: float|int, h: float|int, ease_mode?: string, ease_exponent?: float|int, clamp?: bool}  $body
     * @return array{value: float|int}
     */
    public function map(array $body): array;
}
