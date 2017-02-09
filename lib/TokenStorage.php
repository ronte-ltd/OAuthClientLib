<?php

namespace RonteLtd\OAuthClientLib;

use RonteLtd\OAuthClientLib\Model\Token;

interface TokenStorage
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
