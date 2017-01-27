<?php

namespace Ronte\Messenger\OAuthClientLib;

use Ronte\Messenger\OAuthClientLib\Model\Token;
use Ronte\Messenger\OAuthClientLib\Model\Client;

interface OAuth2Storage
{
    /**
     * Removes token by key if exists.
     *
     * @param mixed $key
     *
     * @return static
     */
    public function removeToken($key);

    /**
     * Gets token by key if exists.
     *
     * @param mixed $key
     *
     * @return Token|null
     */
    public function getToken($key);

    /**
     * Gets client by key if exists.
     *
     * @param mixed $key
     *
     * @return Client|null
     */
    public function getClient($key);

    /**
     * Stores token  by key.
     *
     * @param mixed $key Key to store
     * @param Token $token Token to store
     *
     * @return static
     */
    public function putToken($key, Token $token);

    /**
     * Creates new Token instance
     *
     * @return Token
     */
    public function createToken(): Token;
}
