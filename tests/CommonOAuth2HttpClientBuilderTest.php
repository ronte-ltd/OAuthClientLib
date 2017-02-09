<?php

namespace RonteLtd\OAuthClientLib\Tests;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use RonteLtd\OAuthClientLib\ApiRequestStorage;
use RonteLtd\OAuthClientLib\CommonOAuth2HttpClientBuilder;
use RonteLtd\OAuthClientLib\HttpClientBuilder;
use RonteLtd\OAuthClientLib\Model\Client;
use RonteLtd\OAuthClientLib\Model\Token;
use RonteLtd\OAuthClientLib\ClientStorage;
use RonteLtd\OAuthClientLib\TokenStorage;

class CommonOAuth2HttpClientBuilderTest extends TestCase
{
    public function testTokenFromStorage()
    {
        /*********
         * Token *
         ********/
        $token = $this->getMockTokenFromStorage();
        $token->expects($this->once())
            ->method('hasExpired')
            ->will($this->returnValue(false));
        $token->expects($this->once())
            ->method('getAccessToken')
            ->will($this->returnValue('SOME FOO TOKEN'));

        /**********
         * Client *
         *********/
        $client = $this->getMockBuilder(Client::class)
            ->setMethods(['getAuthContentType', 'getAuthUrl', 'getConsumerKey', 'getConsumerSecret'])
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->exactly(0))->method('getAuthContentType');
        $client->expects($this->exactly(0))->method('getAuthUrl');
        $client->expects($this->exactly(0))->method('getConsumerKey');
        $client->expects($this->exactly(0))->method('getConsumerSecret');

        /*****************
         * OAuth storage *
         ****************/
        $oauthTokenStorage = $this->getMockOAuthTokenStorage();

        $oauthTokenStorage->expects($this->once())
            ->method('getToken')
            ->with('Test Api Key')
            ->will($this->returnValue($token));

        $oauthTokenStorage->expects($this->exactly(0))->method('removeToken');
        $oauthTokenStorage->expects($this->exactly(0))->method('putToken');
        $oauthTokenStorage->expects($this->exactly(0))->method('createToken');

        $oauthClientStorage = $this->getMockOAuthClientStorage();
        $oauthClientStorage->expects($this->once())
          ->method('getClient')
          ->will($this->returnValue($client));

        /**********************
         * Result http client *
         *********************/
        $response = new Response(200, ['Content-Length' => 0]);
        $client = $this
            ->getResultMockHttpClient($response, 'SOME FOO TOKEN');

        /*********************
         * OAuth http client *
         ********************/

        /***********************
         * Http client builder *
         **********************/
        $clientBuilder = $this->getMockHttpClientBuilder([$client]);

        /***********************
         * Api request storage *
         **********************/
        $request = new Request(
          'POST',
          'https://www.google.com',
          ['User-Agent' => 'Test user agent']
        );

        $apiRequestStorage = $this->getMockApiRequestStorage();
        $apiRequestStorage->expects($this->once())
            ->method('push')
            ->with('Test Api Key', $request);
        $apiRequestStorage->expects($this->once())
            ->method('remove')
            ->with('Test Api Key');
        $apiRequestStorage->expects($this->exactly(0))->method('getCount');
        $apiRequestStorage->expects($this->exactly(0))->method('get');

        /********
         * Test *
         *******/
        $this->resultAsserts(
            $apiRequestStorage,
            $oauthClientStorage,
            $oauthTokenStorage,
            $clientBuilder,
            $request,
            $response
        );
    }

    public function testNoTokenFromStorage()
    {
        /*********
         * Token *
         ********/
        $token = $this->getMockTokenFromService('SOME FOO TOKEN', 1485359898);

        /**********
         * Client *
         *********/
        $client = $this->getMockClient();

        /*****************
         * OAuth storage *
         ****************/
        $tokenStorage = $this->getMockOAuthTokenStorage();

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->with('Test Api Key');

        $tokenStorage->expects($this->once())
            ->method('putToken')
            ->with('Test Api Key', $token);

        $tokenStorage->expects($this->once())
            ->method('createToken')
            ->will($this->returnValue($token));

        $tokenStorage->expects($this->exactly(0))->method('removeToken');

        $clientStorage = $this->getMockOAuthClientStorage();
        $clientStorage->expects($this->once())
            ->method('getClient')
            ->will($this->returnValue($client));

        /**********************
         * Result http client *
         *********************/
        $resultResponse = new Response(200);
        $resultClient = $this
            ->getResultMockHttpClient($resultResponse, 'SOME FOO TOKEN');

        /*********************
         * OAuth http client *
         ********************/
        $oauthResponse = new Response(
            200,
            [],
            '{"access_token":"SOME FOO TOKEN","expires_in":1485359898}'
        );
        $oauthClient = $this->getOAuthMockHttpClient($oauthResponse);

        /***********************
         * Http client builder *
         **********************/
        $clientBuilder = $this->getMockHttpClientBuilder([$resultClient, $oauthClient]);

        /***********************
         * Api request storage *
         **********************/
        $request = new Request(
            'POST',
            'https://www.google.com',
            ['User-Agent' => 'Test user agent']
        );

        $apiRequestStorage = $this->getMockApiRequestStorage();
        $apiRequestStorage->expects($this->once())
            ->method('push')
            ->with('Test Api Key', $request);
        $apiRequestStorage->expects($this->once())
            ->method('remove')
            ->with('Test Api Key');
        $apiRequestStorage->expects($this->exactly(0))->method('getCount');
        $apiRequestStorage->expects($this->exactly(0))->method('get');

        /********
         * Test *
         *******/
        $this->resultAsserts(
            $apiRequestStorage,
            $clientStorage,
            $tokenStorage,
            $clientBuilder,
            $request,
            $resultResponse
        );
    }

    public function testTokenFromStorageExpired()
    {
        /**********************
         * Token from storage *
         *********************/
        $tokenFromStorage = $this->getMockTokenFromStorage();
        $tokenFromStorage->expects($this->once())
            ->method('hasExpired')
            ->will($this->returnValue(true));
        $tokenFromStorage->expects($this->exactly(0))->method('getAccessToken');

        /**********************
         * Token from service *
         *********************/
        $tokenFromService = $this->getMockTokenFromService('SOME FOO TOKEN', 3600);

        /**********
         * Client *
         *********/
        $client = $this->getMockClient();

        /*****************
         * OAuth storage *
         ****************/
        $tokenStorage = $this->getMockOAuthTokenStorage();

        $tokenStorage->expects($this->once())
            ->method('getToken')
            ->with('Test Api Key')
            ->will($this->returnValue($tokenFromStorage));

        $tokenStorage->expects($this->once())
            ->method('putToken')
            ->with('Test Api Key', $tokenFromService);

        $tokenStorage->expects($this->once())
            ->method('createToken')
            ->will($this->returnValue($tokenFromService));

        $tokenStorage->expects($this->once())
            ->method('removeToken')
            ->with('Test Api Key');

        $clientStorage = $this->getMockOAuthClientStorage();
        $clientStorage->expects($this->once())
            ->method('getClient')
            ->will($this->returnValue($client));

        /**********************
         * Result http client *
         *********************/
        $resultResponse = new Response(200);
        $resultClient = $this
            ->getResultMockHttpClient($resultResponse, 'SOME FOO TOKEN');

        /*********************
         * OAuth http client *
         ********************/
        $oauthResponse = new Response(
            200,
            [],
            '{"access_token":"SOME FOO TOKEN","expires_in":3600}'
        );
        $oauthClient = $this->getOAuthMockHttpClient($oauthResponse);

        /***********************
         * Http client builder *
         **********************/
        $clientBuilder = $this->getMockHttpClientBuilder([$resultClient, $oauthClient]);

        /***********************
         * Api request storage *
         **********************/
        $request = new Request(
            'POST',
            'https://www.google.com',
            ['User-Agent' => 'Test user agent']
        );

        $apiRequestStorage = $this->getMockApiRequestStorage();
        $apiRequestStorage->expects($this->once())
            ->method('push')
            ->with('Test Api Key', $request);
        $apiRequestStorage->expects($this->once())
            ->method('remove')
            ->with('Test Api Key');
        $apiRequestStorage->expects($this->exactly(0))->method('getCount');
        $apiRequestStorage->expects($this->exactly(0))->method('get');

        /********
         * Test *
         *******/
        $this->resultAsserts(
            $apiRequestStorage,
            $clientStorage,
            $tokenStorage,
            $clientBuilder,
            $request,
            $resultResponse
        );
    }

    public function testTokenFromStorageInvalid()
    {
        /**********************
         * Token from storage *
         *********************/
        $tokenFromStorage = $this->getMockTokenFromStorage();
        $tokenFromStorage->expects($this->once())
            ->method('hasExpired')
            ->will($this->returnValue(false));
        $tokenFromStorage->expects($this->once())
            ->method('getAccessToken')
            ->will($this->returnValue('SOME FOO TOKEN'));

        /**********************
         * Token from service *
         *********************/
        $tokenFromService = $this->getMockTokenFromService('SOME BAR TOKEN', 3600);

        /**********
         * Client *
         *********/
        $client = $this->getMockClient();

        /*****************
         * OAuth storage *
         ****************/
        $tokenStorage = $this->getMockOAuthTokenStorage();

        $tokenStorage->expects($this->at(0))
            ->method('getToken')
            ->with('Test Api Key')
            ->will($this->returnValue($tokenFromStorage));

        $tokenStorage->expects($this->at(2))
            ->method('getToken')
            ->with('Test Api Key');

        $tokenStorage->expects($this->once())
            ->method('putToken')
            ->with('Test Api Key', $tokenFromService);

        $tokenStorage->expects($this->once())
            ->method('removeToken')
            ->with('Test Api Key');

        $tokenStorage->expects($this->once())
            ->method('createToken')
            ->will($this->returnValue($tokenFromService));

        $clientStorage = $this->getMockOAuthClientStorage();
        $clientStorage->expects($this->once())
            ->method('getClient')
            ->will($this->returnValue($client));

        /**********************
         * Result http client *
         *********************/
        $failedResponse = new Response(401);
        $resultResponse = new Response(200);
        $resultClient = $this->getResultMockHttpClient(
            $failedResponse,
            'SOME FOO TOKEN',
            [],
            [function (RequestInterface $request, array $options) use ($resultResponse) {
                $this->assertTrue($request->hasHeader('Authorization'));
                $this->assertSame(
                    'Bearer SOME BAR TOKEN',
                    $request->getHeaderLine('Authorization')
                );

                return $resultResponse;
            }]
        );

        /*********************
         * OAuth http client *
         ********************/
        $oauthResponse = new Response(
            200,
            [],
            '{"access_token":"SOME BAR TOKEN","expires_in":3600}'
        );
        $oauthClient = $this->getOAuthMockHttpClient($oauthResponse);

        /***********************
         * Http client builder *
         **********************/
        $clientBuilder = $this->getMockHttpClientBuilder([$resultClient, $oauthClient]);

        /***********************
         * Api request storage *
         **********************/
        $request0 = new Request(
            'POST',
            'https://www.google.com',
            [
              'User-Agent' => 'Test user agent'
            ]
        );
        $request1 = $request0
          ->withHeader('Authorization', 'Bearer SOME BAR TOKEN')
          ->withBody($request0->getBody());

        $apiRequestStorage = $this->getMockApiRequestStorage();
        $apiRequestStorage->expects($this->at(0))
            ->method('push')
            ->with('Test Api Key', $request0);
        $apiRequestStorage->expects($this->at(3))
            ->method('push')
            ->with('Test Api Key', $request1);
        $apiRequestStorage->expects($this->once())
            ->method('remove')
            ->with('Test Api Key');
        $apiRequestStorage->expects($this->once())
            ->method('getCount')
            ->with('Test Api Key')
            ->will($this->returnValue(1));
        $apiRequestStorage->expects($this->once())
            ->method('get')
            ->with('Test Api Key')
            ->will($this->returnValue($request0));

        /********
         * Test *
         *******/
        $this->resultAsserts(
            $apiRequestStorage,
            $clientStorage,
            $tokenStorage,
            $clientBuilder,
            $request0,
            $resultResponse
        );
    }

    private function getMockToken()
    {
        return $this->getMockBuilder(Token::class)
            ->setMethods(['hasExpired', 'getAccessToken', 'setAccessToken', 'setExpiresIn'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockTokenFromStorage()
    {
        $token = $this->getMockToken();
        $token->expects($this->exactly(0))->method('setAccessToken');
        $token->expects($this->exactly(0))->method('setExpiresIn');
        return $token;
    }

    private function getMockTokenFromService($accessToken, $expiresIn)
    {
        $token = $this->getMockToken();
        $token->expects($this->once())
            ->method('setAccessToken')
            ->with($accessToken);
        $token->expects($this->once())
            ->method('setExpiresIn')
            ->with($expiresIn);
        $token->expects($this->once())
            ->method('getAccessToken')
            ->will($this->returnValue($accessToken));
        $token->expects($this->exactly(0))->method('hasExpired');
        return $token;
    }

    private function getMockClient()
    {
        $client = $this->getMockBuilder(Client::class)
            ->setMethods(['getAuthContentType', 'getAuthUrl', 'getConsumerKey', 'getConsumerSecret'])
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->exactly(2))
            ->method('getAuthContentType')
            ->will($this->returnValue(CommonOAuth2HttpClientBuilder::CONTENT_TYPE_JSON));

        $client->expects($this->once())
            ->method('getAuthUrl')
            ->will($this->returnValue('https://www.google.com/getToken'));

        $client->expects($this->once())
            ->method('getConsumerKey')
            ->will($this->returnValue('Test Consumer Key'));

        $client->expects($this->once())
            ->method('getConsumerSecret')
            ->will($this->returnValue('Test Consumer Secret'));

        return $client;
    }

    private function getMockOAuthClientStorage()
    {
        return $this->getMockBuilder(ClientStorage::class)
            ->setMethods(['getClient'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockOAuthTokenStorage()
    {
        return $this->getMockBuilder(TokenStorage::class)
            ->setMethods(['getToken', 'removeToken', 'putToken', 'createToken'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockHttpClientBuilder(array $clients = [])
    {
        $clientBuilder = $this->getMockBuilder(HttpClientBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['getClient'])
            ->getMock();

        foreach ($clients as $index => $client) {
            $clientBuilder->expects($this->at($index))
                ->method('getClient')
                ->will($this->returnValue($client));
        }

        return $clientBuilder;
    }

    private function getResultMockHttpClient($response, $accessToken, $preHandlers = [], $postHanders = [])
    {
        $handlers = array_merge(
            $preHandlers,
            [function (RequestInterface $request, array $options) use ($response, $accessToken) {
                $this->assertTrue($request->hasHeader('Authorization'));
                $this->assertSame(
                    'Bearer '. $accessToken,
                    $request->getHeaderLine('Authorization')
                );

                return $response;
            }],
            $postHanders
        );
        $mock = new MockHandler($handlers);

        return new HttpClient(['handler' => HandlerStack::create($mock)]);
    }

    private function getOAuthMockHttpClient($response)
    {
        $mock = new MockHandler([function (RequestInterface $request, array $options) use ($response) {
            $this->assertSame(
                'https://www.google.com/getToken',
                (string)$request->getUri()
            );

            $this->assertTrue($request->hasHeader('Authorization'));
            $this->assertSame(
                'Basic VGVzdCBDb25zdW1lciBLZXk6VGVzdCBDb25zdW1lciBTZWNyZXQ=',
                $request->getHeaderLine('Authorization')
            );

            $this->assertTrue($request->hasHeader('Accept'));
            $this->assertSame(
                'application/json',
                $request->getHeaderLine('Accept')
            );

            $this->assertSame(
                '{"grant_type":"client_credentials"}',
                (string)$request->getBody()
            );

            return $response;
        }]);

        return new HttpClient(['handler' => HandlerStack::create($mock)]);
    }

    private function getMockApiRequestStorage()
    {
        return $this->getMockBuilder(ApiRequestStorage::class)
            ->setMethods(['push', 'remove', 'getCount', 'get'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function resultAsserts($apiRequestStorage, $clientStorage, $tokenStorage, $clientBuilder, $req, $resp)
    {
        $clientProvider = new CommonOAuth2HttpClientBuilder(
            $apiRequestStorage,
            $tokenStorage,
            $clientStorage,
            $clientBuilder
        );

        $client = $clientProvider->setApi('Test Api Key')
            ->getClient();

        $newResp = $client->send($req);
        $this->assertEquals($resp, $newResp);
    }
}
