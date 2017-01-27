<?php

namespace Ronte\Messenger\OAuthClientLib\Provider;

interface TimeProvider
{
    /**
     * Returns current timestamp
     *
     * @return int Current timestamp
     */
    public function getTimestamp(): int;
}
