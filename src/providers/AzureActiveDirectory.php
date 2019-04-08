<?php

namespace simpleteam\azureactivedirectoryoauth\providers;

use dukt\social\base\LoginProvider;
use dukt\social\models\Token;
use dukt\social\helpers\SocialHelper;

class AzureActiveDirectory extends LoginProvider
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Azure Active Directory';
    }

    /**
     * @inheritdoc
     */
    public function oauthVersion(): int
    {
        return 2;
    }

    /**
     * @inheritdoc
     */
    public function getManagerUrl()
    {
        return 'https://portal.azure.com';
    }

    /**
     * @inheritdoc
     */
    public function getProfile(Token $token)
    {
        $remoteProfile = $this->getRemoteProfile($token);
        return [
            'id' => isset($remoteProfile['objectId'])?$remoteProfile['objectId']:null,
            'email' => isset($remoteProfile['userPrincipalName'])?$remoteProfile['userPrincipalName']:null,
            'givenName' => isset($remoteProfile['givenName'])?$remoteProfile['givenName']:null,
        ];
    }

    // Protected Methods
    // =========================================================================

    /**
     * Returns the login provider’s OAuth provider.
     *
     * @return \League\OAuth1\Client\Server\Twitter
     */
    public function getOauthProvider()
    {
        $providerInfos = $this->getOauthProviderConfig();

        $config = [
            'clientId' => (isset($providerInfos['options']['clientId']) ? $providerInfos['options']['clientId'] : ''),
            'clientSecret' => (isset($providerInfos['options']['clientSecret']) ? $providerInfos['options']['clientSecret'] : ''),
            'redirectUri' => $this->getRedirectUri(),
        ];

        return new \TheNetworg\OAuth2\Client\Provider\Azure($config);
    }

    /**
     * @inheritdoc
     */
    protected function getRemoteProfile(Token $token)
    {
        $provider = $this->getOauthProvider();
        $provider->tenant = "myorganization";
        return $provider->get("me",$token->token);
    }

    /**
     * Get the redirect URI.
     *      Azure AD doesn't support redirect_uri with query string - so we need to encode query in the base url
     * @return string
     */
    public function getRedirectUri(): string
    {
        $url = SocialHelper::siteActionUrl('social/login-accounts/callback');
        $parsedUrl = parse_url($url);

        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $query);
            return $parsedUrl['scheme'].'://'.$parsedUrl['host'].'/'.$query['p'];
        }

        return $url;
    }

    public function getIconUrl()
    {
        return "http://svgshare.com/i/40a.svg";
    }

    public function getDefaultUserFieldMapping(): array
    {
        return [];
    }
}