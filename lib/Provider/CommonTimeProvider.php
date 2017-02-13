<?php

namespace RonteLtd\OAuthClientLib\Provider;

class CommonTimeProvider implements TimeProvider
{
    public static function __set_state(array $properties)
    {
        return new CommonTimeProvider();
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp(): int
    {
        return time();
    }
}
