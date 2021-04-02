<?php

declare(strict_types=1);

/*
 * This file is part of the StyleCI SDK.
 *
 * (c) Graham Campbell Technology Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests;

use GrahamCampbell\TestBenchCore\MockeryTrait;
use GuzzleHttp\ClientInterface;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use StyleCI\SDK\Client;

class ClientTest extends TestCase
{
    use MockeryTrait;

    public function testCanBeInstantiated()
    {
        $this->assertInstanceOf(Client::class, new Client());
    }

    public function testCanGetFixers()
    {
        $client = new Client($mock = Mockery::mock(ClientInterface::class));

        $mock->shouldReceive('request')->once()->with('GET', 'fixers', [])->andReturn($response = Mockery::mock(ResponseInterface::class));

        $response->shouldReceive('getBody')->once()->andReturn('[{"name":"align_double_arrow","description":"Align double arrow symbols in consecutive lines.","risky":false,"conflict":"unalign_double_arrow","aliases":[]}]');

        $this->assertSame([['name' => 'align_double_arrow', 'description' => 'Align double arrow symbols in consecutive lines.', 'risky' => false, 'conflict' => 'unalign_double_arrow', 'aliases' => []]], $client->fixers());
    }

    public function testCanGetPresets()
    {
        $client = new Client($mock = Mockery::mock(ClientInterface::class));

        $mock->shouldReceive('request')->once()->with('GET', 'presets', [])->andReturn($response = Mockery::mock(ResponseInterface::class));

        $response->shouldReceive('getBody')->once()->andReturn('[{"title":"PSR1","name":"psr1","fixers":["encoding","full_opening_tag","psr4"]}]');

        $this->assertSame([['title' => 'PSR1', 'name' => 'psr1', 'fixers' => ['encoding', 'full_opening_tag', 'psr4']]], $client->presets());
    }
}
