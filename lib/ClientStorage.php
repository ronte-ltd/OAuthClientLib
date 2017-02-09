<?php

namespace RonteLtd\OAuthClientLib;

use RonteLtd\OAuthClientLib\Model\Token;
use RonteLtd\OAuthClientLib\Model\Client;

interface ClientStorage
{
    /**
     * Returns client by key if exists. If not exists returns null.
     * If key not provided -- returns default client;
     *
     * @param mixed|null $key
     *
     * @return Client|null
     */
    public function getClient($key = null);
}
