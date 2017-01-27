<?php

namespace Ronte\Messenger\OAuthClientLib;

use GuzzleHttp\ClientInterface;

interface HttpClientBuilder
{
    /**
     * Builds http client.
     *
     * @return ClientInterface
     */
    public function getClient(): ClientInterface;
}
