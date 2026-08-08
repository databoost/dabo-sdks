<?php

declare(strict_types=1);

namespace Databoost\Sro\Tests;

use Databoost\Sro\SroClient;
use Databoost\Sro\StatefulSroClient;
use PHPUnit\Framework\TestCase;

final class SroClientHttpFactoryTest extends TestCase
{
    public function test_http_factory_returns_stateful_client(): void
    {
        $client = SroClient::http('http://127.0.0.1:8080', 'token', 'tenant');
        $this->assertInstanceOf(StatefulSroClient::class, $client);
    }
}
