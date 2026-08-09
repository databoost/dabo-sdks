<?php

declare(strict_types=1);

namespace Databoost\Sro\Tests;

use Databoost\Sro\Client;
use PHPUnit\Framework\TestCase;

final class ClientHttpFactoryTest extends TestCase
{
    public function test_http_factory_returns_client(): void
    {
        $client = Client::http('https://sro.databoost.com', 'token', 'tenant');
        $this->assertInstanceOf(Client::class, $client);
    }
}
