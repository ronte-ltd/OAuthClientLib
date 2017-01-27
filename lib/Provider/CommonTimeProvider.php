<?php

namespace RonteLtd\OAuthClientLib\Provider;

class CommonTimeProvider implements TimeProvider
{
    /**
     * {@inheritdoc}
     */
    public function getTimestamp(): int
    {
        return time();
    }
}
