<?php

namespace RonteLtd\OAuthClientLib;

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
