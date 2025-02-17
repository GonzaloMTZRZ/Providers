<?php

namespace SocialiteProviders\Exment;

use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'EXMENT';

    protected $scopeSeparator = ' ';

    protected $scopes = ['me'];

    public static function additionalConfigKeys(): array
    {
        return ['exment_uri'];
    }

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase($this->getBaseUri().'/oauth/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return $this->getBaseUri().'/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get($this->getBaseUri().'/api/me', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'       => $user['id'],
            'nickname' => $user['value']['user_code'],
            'name'     => $user['value']['user_name'],
            'email'    => $user['value']['email'],
            'avatar'   => null,
        ]);
    }

    /**
     * Get Exment base URI.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function getBaseUri(): string
    {
        $exmentUri = $this->getConfig('exment_uri');
        if ($exmentUri === null) {
            throw new InvalidArgumentException('Please config Exment URI.');
        }

        return rtrim($exmentUri, '/');
    }
}
