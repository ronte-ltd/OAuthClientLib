<?php

namespace RonteLtd\OAuthClientLib\Model;

use RonteLtd\OAuthClientLib\Provider\TimeProvider;

class CommonToken implements Token
{
    private $timeProvider;
    private $accessToken;
    private $expiresAt;

    public static function __set_state(array $properties)
    {
        if (isSet($properties['timeProvider'])) {
            $prop = new CommonToken($properties['timeProvider']);
            if (isSet($properties['accessToken'])) {
                $prop->accessToken = $properties['accessToken'];
            }
            if (isSet($properties['expiresAt'])) {
                $prop->expiresAt = $properties['expiresAt'];
            }
            return $prop;
        }
        return null;
    }

    public function __construct(TimeProvider $timeProvider)
    {
        $this->timeProvider = $timeProvider;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function __set_state(array $properties)
    {
        $obj =  new self($properties['timeProvider']);
        $obj->accessToken = $properties['accessToken'];
        $obj->expiresAt = $properties['expiresAt'];
        return $obj;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function setAccessToken(string $token)
    {
        $this->accessToken = $token;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setExpiresIn(int $expiresIn)
    {
        $this->expiresAt = $this->timeProvider->getTimestamp() + $expiresIn;
        return $this;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getAccessToken(): string
    {
        return isSet($this->accessToken) ? $this->accessToken : '';
    }

    /**
     * {@inheritdoc}
     */
    public function hasExpired(): bool
    {
        return empty($this->expiresAt)
            || $this->expiresAt <= $this->timeProvider->getTimestamp();
    }
}
