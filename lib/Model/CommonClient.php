<?php

namespace RonteLtd\OAuthClientLib\Model;

class CommonClient implements Client
{
    protected $contentType;
    protected $url;
    protected $consumerKey;
    protected $consumerSecret;

    /**
     * {@inheritdoc}
     */
    public function getAuthContentType(): string
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthUrl(): string
    {
        return $this->url;
    }

    /**
     * {@inheritdoc}
     */
    public function getConsumerKey(): string
    {
        return $this->consumerKey;
    }

    /**
     * {@inheritdoc}
     */
    public function getConsumerSecret(): string
    {
        return $this->consumerSecret;
    }
}
