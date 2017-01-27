<?php

namespace Ronte\Messenger\OAuthClientLib\Model;

use Ronte\Messenger\OAuthClientLib\Provider\TimeProvider;

class CommonToken implements Token
{
    private $timeProvider;
    private $accessToken;
    private $expiresAt;

    public function __construct(TimeProvider $timeProvider)
    {
        $this->timeProvider = $timeProvider;
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
