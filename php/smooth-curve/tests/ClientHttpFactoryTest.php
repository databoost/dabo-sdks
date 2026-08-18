<?php
// © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.

declare(strict_types=1);

namespace Databoost\SmoothCurve\Tests;

use Databoost\SmoothCurve\Client;
use Databoost\SmoothCurve\Engine;
use Databoost\SmoothCurve\HttpEngine;
use PHPUnit\Framework\TestCase;

final class ClientHttpFactoryTest extends TestCase
{
    public function test_http_factory_returns_client(): void
    {
        $client = Client::http('https://smocur.example.test', 'token', 'tenant');
        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(HttpEngine::class, $client->engine());
    }

    public function test_client_delegates_curve_and_map(): void
    {
        $engine = new class implements Engine {
            /** @var list<string> */
            public array $calls = [];

            /** @var list<array<string, mixed>> */
            public array $bodies = [];

            public function curve(array $body): array
            {
                $this->calls[] = 'curve';
                $this->bodies[] = $body;

                return ['t' => 0.5];
            }

            public function map(array $body): array
            {
                $this->calls[] = 'map';
                $this->bodies[] = $body;

                return ['value' => 150];
            }
        };

        $client = new Client($engine);

        $curve = $client->curve(['a' => 0, 'b' => 100, 'c' => 50]);
        $this->assertSame(0.5, $curve['t']);

        $mapped = $client->map([
            'a' => 0,
            'b' => 100,
            'c' => 50,
            'g' => 100,
            'h' => 200,
            'ease_mode' => 'linear',
            'ease_exponent' => 2,
            'clamp' => true,
        ]);
        $this->assertSame(150, $mapped['value']);
        $this->assertSame(['curve', 'map'], $engine->calls);
        $this->assertSame(['a' => 0, 'b' => 100, 'c' => 50], $engine->bodies[0]);
        $this->assertSame('linear', $engine->bodies[1]['ease_mode']);
    }
}
