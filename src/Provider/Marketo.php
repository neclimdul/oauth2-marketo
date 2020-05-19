<?php

namespace NecLimDul\OAuth2\Client\Provider;

use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use NecLimDul\OAuth2\Client\Token\AccessToken as MarketoAccessToken;
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
        return new MarketoAccessToken($response);
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

    /**
     * {@inheritDoc}
     */
    public function getBaseAuthorizationUrl()
    {
        throw new \Exception('Not Implemented', 501);
    }

    /**
     * {@inheritDoc}
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        throw new \Exception('Not Implemented', 501);
    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultScopes()
    {
        throw new \Exception('Not Implemented', 501);
    }

    /**
     * {@inheritDoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        throw new \Exception('Not Implemented', 501);
    }

}
