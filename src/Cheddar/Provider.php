<?php

namespace SocialiteProviders\Cheddar;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'CHEDDAR';

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://api.cheddarapp.com/oauth/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return 'https://api.cheddarapp.com/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            'https://api.cheddarapp.com/v1/me',
            [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer '.$token,
                ],
            ]
        );

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'    => $user['id'], 'nickname' => $user['username'],
            'name'  => $user['first_name'].' '.$user['last_name'],
            'email' => null, 'avatar' => null,
        ]);
    }
}
