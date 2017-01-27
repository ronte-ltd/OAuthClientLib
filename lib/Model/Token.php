<?php

namespace RonteLtd\OAuthClientLib\Model;

interface Token
{
    /**
     * Sets access token.
     *
     * @param string $token Access token
     *
     * @return static
     */
    public function setAccessToken(string $token);

    /**
     * Sets number of seconds when it expires.
     *
     * @param int $expiresIn number of seconds, when token expires
     *
     * @return static
     */
    public function setExpiresIn(int $expiresIn);

    /**
     * Gets access token.
     *
     * @return string Access token
     */
    public function getAccessToken(): string;

    /**
     * Return if this token has expired.
     *
     * @return bool If this token has expired
     */
    public function hasExpired(): bool;
}
