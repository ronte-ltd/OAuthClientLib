<?php

namespace RonteLtd\OAuthClientLib;

use GuzzleHttp\ClientInterface;

interface OAuthClientProvider
{
    /**
     * Set api to get access token from.
     *
     * @param mixed $apiKey Some identifier of api to use
     * @return static
     * @throws \LogicException if no information about api found by key.
     */
    public function setApi($apiKey);

    /**
     * Returns access token for API.
     *
     * @param Client|null $httpClient Base http client to "upgrade"
     *
     * @return Client $accessToken
     * @throws OAuthClientException
     */
    public function provideClient(ClientInterface $httpClient = null): ClientInterface;
}
