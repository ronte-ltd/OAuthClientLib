<?php

namespace RonteLtd\OAuthClientLib;

use Psr\Http\Message\RequestInterface;
use RonteLtd\OAuthClientLib\Exception\RequestNotFoundException;

class InMemoryApiRequestStorage implements ApiRequestStorage
{
    private $storage = [];

    /**
     * {@inheritdoc}
     */
    public function push($key, RequestInterface $request)
    {
        if (empty($this->storage[$key])) {
            $this->storage[$key] = ['data' => null, 'count' => 0];
        }
        $this->storage[$key]['count']++;
        $this->storage[$key]['data'] = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount($key): int
    {
        return empty($this->storage[$key]) ? 0 : $this->storage[$key]['count'];
    }

    /**
     * {@inheritdoc}
     */
    public function get($key): RequestInterface
    {
        if (empty($this->storage[$key])) {
            throw new RequestNotFoundException('Key: ' . var_export($key, true));
        }
        return $this->storage[$key]['data'];
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        unset($this->storage[$key]);
        return $this;
    }
}
