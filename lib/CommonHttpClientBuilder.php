<?php

namespace RonteLtd\OAuthClientLib;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;

class CommonHttpClientBuilder implements HttpClientBuilder
{
    /**
     * {@inheritdoc}
     */
    public function getClient(): ClientInterface
    {
        return new Client();
    }
}
