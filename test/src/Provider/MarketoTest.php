<?php

namespace NecLimDul\OAuth2\Client\Test\Provider;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use NecLimDul\OAuth2\Client\Provider\Marketo;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;


class MarketoTest extends TestCase
{
    protected $provider;

    /**
     * @var \GuzzleHttp\ClientInterface|\Prophecy\Prophecy\ObjectProphecy
     */
    protected $client;

    protected function setUp(): void
    {
        $this->provider = new Marketo(
            [
                'clientId' => 'mock_client_id',
                'clientSecret' => 'mock_secret',
                'baseUrl' => 'https://abc-123-456.example.com',
            ]
        );
        $this->client = $this->prophesize(ClientInterface::class);
        $this->provider->setHttpClient($this->client->reveal());
    }

    public function testGetBaseAccessTokenUrl()
    {
        $params = [];
        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);
        $this->assertEquals('/identity/oauth/token', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $response = new Response(
            200,
            ['content-type' => 'json'],
            '{"access_token": "mock_access_token", "expires_in": 3600}'
        );
        $this->client->send(Argument::any())
            ->willReturn($response);

        $token = $this->provider->getAccessToken('client_credentials');
        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertNull($token->getRefreshToken());
    }

    public function testCheckResponseThrowsIdentityProviderException()
    {
        $method = new class extends Marketo {
            public function checkResponse(ResponseInterface $response, $data)
            {
                parent::checkResponse($response, $data);
            }
        };

        $response = new Response(
            401,
            ['content-type' => 'json'],
            '{"error": "unauthorized", "error_description": "No client with requested id: abc123"}'
        );

        $data = [
            'error' => 'unauthorized',
            'error_description' => 'No client with requested id: abc123'
        ];

        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('unauthorized');
        $method->checkResponse($response, $data);
    }

}
