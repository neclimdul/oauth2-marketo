<?php

namespace NecLimDul\OAuth2\Client\Provider;

use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class Marketo extends AbstractProvider
{
    protected $baseUrl;

    /**
     * The base marketo API url.
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * {@inheritDoc}
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->getBaseUrl() . '/identity/oauth/token';
    }

    /**
     * {@inheritDoc}
     */
    protected function createAccessToken(array $response, AbstractGrant $grant)
    {
        return new \NecLimDul\OAuth2\Client\Token\AccessToken($response);
    }

    /**
     * {@inheritDoc}
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw new IdentityProviderException(
                $data['error'] ?: $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }


    public function getBaseAuthorizationUrl()
    {
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
    }

    protected function getDefaultScopes()
    {
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
    }
}
