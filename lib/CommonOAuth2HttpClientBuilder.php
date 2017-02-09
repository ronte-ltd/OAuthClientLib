<?php

namespace RonteLtd\OAuthClientLib;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Middleware;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RonteLtd\OAuthClientLib\Exception\ClientNotFoundException;
use RonteLtd\OAuthClientLib\Exception\ClientException;
use RonteLtd\OAuthClientLib\Exception\WrongClientException;
use RonteLtd\OAuthClientLib\Model\Client;
use RonteLtd\OAuthClientLib\Model\Token;

class CommonOAuth2HttpClientBuilder implements ConfigurableOAuth2HttpClientBuilder
{
    const GUZZLE_CLIENT_HANDLER_CONFIG = 'handler';
    const CONFIG_MAX_RETRYS = 'config-max-retrys';
    const MAX_RETRYS = 2;

    const CONTENT_TYPE_FORM = 'form';
    const CONTENT_TYPE_JSON = 'json';
    const CONTENT_TYPE_MAP = [
        self::CONTENT_TYPE_FORM => 'grant_type=client_credentials',
        self::CONTENT_TYPE_JSON => '{"grant_type":"client_credentials"}',
    ];

    private $requestStorage;
    private $clientStorage;
    private $tokenStorage;
    private $clientBuilder;
    private $currentApiKey;
    private $client;

    public function __construct(
        ?ApiRequestStorage $requestStorage,
        ?TokenStorage $tokenStorage,
        ?ClientStorage $clientStorage,
        ?HttpClientBuilder $clientBuilder,
        array $config = []
    ) {
        $this->requestStorage = $requestStorage;
        $this->tokenStorage = $tokenStorage;
        $this->clientStorage = $clientStorage;
        $this->clientBuilder = $clientBuilder;
        $this->setDefaultOptions();
        foreach ($config as $name => $value) {
            $this->config[$name] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setApi($apiKey)
    {
        $this->currentApiKey = $apiKey;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setApiKey($apiKey)
    {
        $this->currentApiKey = $apiKey;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function getClient(ClientInterface $httpClient = null): ClientInterface
    {
        $currentApiKey = $this->currentApiKey;

        if ($this->client) {
            $currentClient = $this->client;
        } elseif ($this->clientStorage) {
            $currentClient = $this->clientStorage->getClient($currentApiKey);
        } else {
            $currentClient = null;
        }
        if (!$currentClient) {
            throw new ClientNotFoundException();
        }

        if (!$httpClient) {
            $httpClient = $this->clientBuilder->getClient();
        }

        $httpClient->getConfig(self::GUZZLE_CLIENT_HANDLER_CONFIG)
            ->push(
                Middleware::mapRequest(function (RequestInterface $r) use ($currentApiKey, $currentClient) {
                    $this->requestStorage->push($currentApiKey, $r);
                    if (!$r->hasHeader('Authorization')) {
                        $token = $this->getToken($currentApiKey, $currentClient);
                        $r = $r->withHeader('Authorization', 'Bearer ' . $token->getAccessToken());
                    }
                    return $r;
                })
            );
        $httpClient->getConfig(self::GUZZLE_CLIENT_HANDLER_CONFIG)
            ->push(
                Middleware::mapResponse(function (ResponseInterface $r) use ($currentApiKey, $currentClient, $httpClient) {
                    if (
                        $r->getStatusCode() == 401
                        && $this->requestStorage->getCount($currentApiKey) < $this->config[self::CONFIG_MAX_RETRYS]
                    ) {
                        try {
                            $token = $this->flushToken($currentApiKey)
                                ->getToken($currentApiKey, $currentClient);
                        } catch (OAuthClientException $e) {
                            return $r;
                        }
                        try {
                            $req = $this->requestStorage->get($currentApiKey);
                        } catch (RequestNotFoundException $e) {
                            return $r;
                        }
                        $req = $req->withHeader('Authorization', 'Bearer ' . $token->getAccessToken());
                        return $httpClient->send($req);
                    }
                    $this->requestStorage->remove($currentApiKey);
                    return $r;
                })
            );
        return $httpClient;
    }

    private function setDefaultOptions()
    {
        $this->config = [
            self::CONFIG_MAX_RETRYS => self::MAX_RETRYS,
        ];
    }

    private function flushToken($currentApiKey)
    {
        if ($this->tokenStorage) {
            $this->tokenStorage->removeToken($currentApiKey);
        }
        return $this;
    }

    private function getToken($currentApiKey, Client $client)
    {
        if ($this->tokenStorage) {
            $token = $this->tokenStorage->getToken($currentApiKey);
        } else {
            $token = null;
        }
        if (!$token || $token->hasExpired()) {
            if ($token) {
              $this->flushToken($currentApiKey);
            }
            $httpClient = $this->clientBuilder->getClient();
            if (empty(self::CONTENT_TYPE_MAP[$client->getAuthContentType()])) {
                throw new WrongClientException(
                    'Unexpected encoding. Expected are: ["'
                    . implode('", "', array_keys(self::CONTENT_TYPE_MAP))
                    . '"].'
                );
            }

            try {
                $resp = $httpClient->post(
                    $client->getAuthUrl(),
                    [
                        'body' => self::CONTENT_TYPE_MAP[$client->getAuthContentType()],
                        'auth' => [
                            $client->getConsumerKey(),
                            $client->getConsumerSecret()
                        ],
                        'headers' => ['Accept' => 'application/json']
                    ]
                );
            } catch (TransferException $e) {
                throw new ClientException($e->getMessage(), $e->getCode(), $e);
            }

            if ($this->tokenStorage) {
                $token = $this->buildToken((string)$resp->getBody());
                $this->tokenStorage->putToken($currentApiKey, $token);
            }
        }

        return $token;
    }

    private function buildToken(string $rowData): Token
    {
        $data = json_decode($rowData, true);
        if (empty($data['access_token']) || empty($data['expires_in'])) {
            throw new ClientException('Unexpected response: ' . $rowData);
        }

        $token = $this->tokenStorage->createToken();
        $token->setAccessToken($data['access_token']);
        $token->setExpiresIn($data['expires_in']);
        return $token;
    }
}
