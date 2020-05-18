<?php

namespace NecLimDul\OAuth2\Client\Test\Provider;

use GuzzleHttp\ClientInterface;
use NecLimDul\OAuth2\Client\Provider\Marketo;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseInterface;


class MarketoTest extends TestCase
{
    use ProphecyTrait;

    protected $provider;

    protected function setUp(): void
    {
        $this->provider = new Marketo(
            [
                'clientId' => 'mock_client_id',
                'clientSecret' => 'mock_secret',
                'baseUrl' => 'https://abc-123-456.example.com',
            ]
        );
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
        $response = $this->prophesize(ResponseInterface::class);
        $response->getBody()
            ->willReturn(
                '{"access_token": "mock_access_token", "expires_in": 3600}'
            );
        $response->getHeader('content-type')
            ->willReturn(['content-type' => 'json']);
        $response->getStatusCode()
            ->willReturn(200);

        $client = $this->prophesize(ClientInterface::class);
        $client->send(Argument::any())
            ->willReturn($response->reveal());

        $this->provider->setHttpClient($client->reveal());

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

        $response = $this->prophesize(ResponseInterface::class);
        $response->getBody()
            ->willReturn(
                '{"error": "unauthorized", "error_description": "No client with requested id: abc123"}'
            );
        $response->getStatusCode()
            ->willReturn(401);

        $data = [
            'error' => "unauthorized",
            "error_description" => "No client with requested id: abc123"
        ];

        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('unauthorized');
        $method->checkResponse($response->reveal(), $data);
    }

}
