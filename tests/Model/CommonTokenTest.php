<?php

namespace RonteLtd\OAuthClientLib\Tests\Model;

use RonteLtd\OAuthClientLib\Model\CommonToken;
use RonteLtd\OAuthClientLib\Provider\TimeProvider;
use PHPUnit\Framework\TestCase;

class CommonTokenTest extends TestCase
{
    public function testHasExpired()
    {
        $timeProvider = $this->getMockBuilder(TimeProvider::class)
            ->setMethods(['getTimestamp'])
            ->disableOriginalConstructor()
            ->getMock();

        $timeProvider->expects($this->at(0))
            ->method('getTimestamp')
            ->will($this->returnValue(1485348208));

        $timeProvider->expects($this->at(1))
           ->method('getTimestamp')
           ->will($this->returnValue(1485348210));

        $timeProvider->expects($this->at(2))
           ->method('getTimestamp')
           ->will($this->returnValue(1485348220));

        $client = new CommonToken($timeProvider);
        $client->setExpiresIn(10);

        $this->assertFalse($client->hasExpired());
        $this->assertTrue($client->hasExpired());
    }
}
