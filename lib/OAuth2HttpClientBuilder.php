<?php

namespace RonteLtd\OAuthClientLib;

use GuzzleHttp\ClientInterface;

interface OAuth2HttpClientBuilder extends HttpClientBuilder
{
    /**
     * Set api to get access token from.
     *
     * @param mixed $apiKey Some identifier of api to use
     * @return static
     * @throws \LogicException if no information about api found by key.
     */
    public function setApi($apiKey);
}
