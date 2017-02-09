<?php

namespace RonteLtd\OAuthClientLib;

use RonteLtd\OAuthClientLib\Model\Client;

interface ConfigurableOAuth2HttpClientBuilder extends OAuth2HttpClientBuilder
{
    /**
     * Sets only api key (without setting api environment).
     *
     * @param mixed $apiKey
     *
     * @return static
     */
    public function setApiKey($apiKey);

    /**
     * Set current api client.
     *
     * @param Client $client
     *
     * @return static
     */
    public function setClient(Client $client);
}
