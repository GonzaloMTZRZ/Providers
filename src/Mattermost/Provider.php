<?php

namespace SocialiteProviders\Mattermost;

use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'MATTERMOST';

    protected $scopeSeparator = ' ';

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase($this->getInstanceUri().'oauth/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return $this->getInstanceUri().'oauth/access_token';
    }

    /**
     * Mattermost API version. Used for user avatar URL and more.
     */
    protected $apiVersion = 'v4';

    /**
     * Mattermost.
     *
     * @return string
     */
    protected function getAPIVersion()
    {
        return $this->getConfig('api_version', $this->apiVersion);
    }

    protected function getAPIBase()
    {
        return $this->getInstanceUri()."api/{$this->getAPIVersion()}";
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            "{$this->getInstanceUri()}api/{$this->getAPIVersion()}/users/me",
            [
                RequestOptions::HEADERS => [
                    'Authorization' => 'BEARER '.$token,
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
            'id'       => $user['id'],
            'nickname' => $user['nickname'],
            'name'     => $user['username'],
            'email'    => $user['email'],
            'avatar'   => isset($user['last_picture_update']) ? "{$this->getAPIBase()}/users/{$user['id']}/image?time={$user['last_picture_update']}" : '',
        ]);
    }

    protected function getInstanceUri()
    {
        $uri = $this->getConfig('instance_uri');
        if (! $uri) {
            throw new InvalidArgumentException('No instance_uri. ENV['.self::IDENTIFIER.'_INSTANCE_URI]=https://mm.example.com/ must be provided.');
        }

        return $uri;
    }

    public static function additionalConfigKeys(): array
    {
        return ['api_version', 'instance_uri'];
    }
}
