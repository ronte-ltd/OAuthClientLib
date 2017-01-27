<?php

namespace Ronte\Messenger\OAuthClientLib;

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
