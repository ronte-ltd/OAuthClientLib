<?php

namespace RonteLtd\OAuthClientLib\Tests;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Request;
use RonteLtd\OAuthClientLib\InMemoryApiRequestStorage;
use RonteLtd\OAuthClientLib\Exception\RequestNotFoundException;

class InMemoryApiRequestStorageTest extends TestCase
{
    public function testAll()
    {
        $storage = new InMemoryApiRequestStorage();

        $storage->push('Key1', new Request('POST', 'http://httpbin.org/put'));

        $this->assertSame(1, $storage->getCount('Key1'));

        $req = new Request('GET', 'https://www.google.com');

        $storage->push('Key1', $req);

        $this->assertSame(2, $storage->getCount('Key1'));
        $this->assertSame(0, $storage->getCount('Key2'));

        $this->assertEquals($req, $storage->get('Key1'));

        $this->assertEquals(
            0,
            $storage->remove('Key1')->remove('Key2')->getCount('Key1')
        );
    }

    public function testException()
    {
        $this->expectException(RequestNotFoundException::class);
        $storage = new InMemoryApiRequestStorage();
        $storage->get('Key1');
    }
}
