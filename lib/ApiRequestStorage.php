<?php

namespace RonteLtd\OAuthClientLib;

use Psr\Http\Message\RequestInterface;
use RonteLtd\OAuthClientLib\Exception\RequestNotFoundException;

interface ApiRequestStorage
{
    /**
     * Stores request by key.
     * If no request for this key has been stored -- sets counter for this key to one,
     * otherwise -- increase it by one.
     *
     * @param mixed $key Key to store
     * @param RequestInterface $request Request to store by key.
     *
     * @return statuc
     */
    public function push($key, RequestInterface $request);

    /**
     * Returns count of pushes for current key.
     * If nothing was stored my this key -- returns 0;
     *
     * @param mixed $key Key to check
     *
     * @return int Count of pushes
     */
    public function getCount($key): int;

    /**
     * Returns last request stored by key.
     * If no one -- throws RequestNotFoundException.
     *
     * @param mixed $key Key to check
     *
     * @return RequestInterface Request, stroed by key
     *
     * @throws RequestNotFoundException If nothign stored for this key.
     */
    public function get($key): RequestInterface;

    /**
     * Removes data stored by key if exists. Sets counter to 0 for this key.
     *
     * @param mixed $key Key to remove
     *
     * @return static
     */
    public function remove($key);
}
