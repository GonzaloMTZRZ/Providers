<?php

namespace SocialiteProviders\Box;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'BOX';

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://app.box.com/api/oauth2/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return 'https://app.box.com/api/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            'https://www.box.com/api/2.0/users/me',
            [
                RequestOptions::HEADERS => [
                    'Accept'        => 'application/json',
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
            'id'    => $user['id'], 'nickname' => null, 'name' => $user['name'],
            'email' => $user['login'], 'avatar' => $user['avatar_url'],
        ]);
    }
}
