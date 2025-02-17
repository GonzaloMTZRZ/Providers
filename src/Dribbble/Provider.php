<?php

namespace SocialiteProviders\Dribbble;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'DRIBBBLE';

    protected $scopes = ['public'];

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://dribbble.com/oauth/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return 'https://dribbble.com/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://api.dribbble.com/v2/user', [
            RequestOptions::QUERY => [
                'access_token' => $token,
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
            'id'     => $user['id'], 'nickname' => $user['login'],
            'name'   => $user['name'], 'email' => Arr::get($user, 'email'),
            'avatar' => $user['avatar_url'],
        ]);
    }
}
