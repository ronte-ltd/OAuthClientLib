<?php

namespace Ronte\Messenger\OAuthClientLib\Model;

interface Client
{
    /**
     * Gets content type, accepted by auth service, ig json or form.
     * Content type MUST be one of supported by \Ronte\Messenger\OAuthClientLib\CommonOAuthClientProvider!
     *
     * @return string Content type
     */
    public function getAuthContentType(): string;

    /**
     * Gets valig url to get access_token.
     *
     * @return string Url
     */
    public function getAuthUrl(): string;

    /**
     * Gets consumer key (client id) for service.
     *
     * @return string Consumer key
     */
    public function getConsumerKey(): string;

    /**
     * Gets consumer secret (client secret) for service.
     *
     * @return string Consumer secret
     */
    public function getConsumerSecret(): string;
}
