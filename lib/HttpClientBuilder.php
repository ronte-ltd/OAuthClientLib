<?php

namespace RonteLtd\OAuthClientLib;

use GuzzleHttp\ClientInterface;

interface HttpClientBuilder
{
    /**
     * Builds http client.
     *
     * @param ClientInterface|null $baseClient Base http client to build from
     *
     * @return ClientInterface
     */
    public function getClient(ClientInterface $baseClient = null): ClientInterface;
}
