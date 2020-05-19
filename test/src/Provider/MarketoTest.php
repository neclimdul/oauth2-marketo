<?php

namespace NecLimDul\OAuth2\Client\Test\Provider;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use League\OAuth2\Client\Token\AccessToken;
use NecLimDul\OAuth2\Client\Provider\Marketo;
use NecLimDul\OAuth2\Client\Token\AccessToken as MarketoAccessToken;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;

/**
 * @coversDefaultClass \NecLimDul\OAuth2\Client\Provider\Marketo
 */
class MarketoTest extends TestCase
{
    /**
     * @var \NecLimDul\OAuth2\Client\Provider\Marketo
     */
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

    /**
     * @covers ::getBaseAccessTokenUrl
     * @covers ::getBaseUrl
     */
    public function testGetBaseAccessTokenUrl()
    {
        $this->assertEquals(
            'https://abc-123-456.example.com/identity/oauth/token',
            $this->provider->getBaseAccessTokenUrl([])
        );
    }

    /**
     * @covers ::createAccessToken
     */
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
        $this->assertInstanceOf(MarketoAccessToken::class, $token);
        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertNull($token->getRefreshToken());
    }

    /**
     * @covers ::checkResponse
     */
    public function testCheckResponse()
    {
        $provider = new class extends Marketo {
            public function checkResponse(ResponseInterface $response, $data)
            {
                parent::checkResponse($response, $data);
            }
        };

        $response = new Response(
            200,
            ['content-type' => 'json'],
            '{"access_token": "mock_access_token", "expires_in": 3600}'
        );

        $this->assertNull($provider->checkResponse($response, []));
    }

    /**
     * @covers ::checkResponse
     */
    public function testCheckResponseThrowsIdentityProviderException()
    {
        $provider = new class extends Marketo {
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
        $provider->checkResponse($response, $data);
    }

    /**
     * @covers ::getBaseAuthorizationUrl
     */
    public function testGetBaseAuthorizationUrl()
    {
        $this->expectExceptionCode(501);
        $this->expectExceptionMessage('Not Implemented');
        $this->provider->getBaseAuthorizationUrl();
    }

    /**
     * @covers ::getResourceOwnerDetailsUrl
     */
    public function testGetResourceOwnerDetailsUrl()
    {
        $this->expectExceptionCode(501);
        $this->expectExceptionMessage('Not Implemented');
        $this->provider->getResourceOwnerDetailsUrl(new MarketoAccessToken(['access_token' => 123]));
    }

    /**
     * @covers ::getDefaultScopes
     */
    public function testGetDefaultScopes()
    {
        $provider = new class extends Marketo {
            public function getDefaultScopes()
            {
                parent::getDefaultScopes();
            }
        };

        $this->expectExceptionCode(501);
        $this->expectExceptionMessage('Not Implemented');
        $provider->getDefaultScopes();
    }

    /**
     * @covers ::createResourceOwner
     */
    public function testCreateResourceOwner()
    {
        $provider = new class extends Marketo {
            public function createResourceOwner(array $response, AccessToken $token)
            {
                parent::createResourceOwner($response, $token);
            }
        };

        $this->expectExceptionCode(501);
        $this->expectExceptionMessage('Not Implemented');
        $provider->createResourceOwner([], new MarketoAccessToken(['access_token' => 123]));
    }

}
