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
    public function getName()
    {
        return 'Azure Active Directory';
    }

    /**
     * @inheritdoc
     */
    public function oauthVersion()
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
    protected function getOauthProvider()
    {
        $providerInfos = $this->getInfos();

        $config = [
            'clientId' => (isset($providerInfos['clientId']) ? $providerInfos['clientId'] : ''),
            'clientSecret' => (isset($providerInfos['clientSecret']) ? $providerInfos['clientSecret'] : ''),
            'redirectUri' => $this->getRedirectUri(),
        ];


        return new \TheNetworg\OAuth2\Client\Provider\Azure($config);
    }

    /**
     * @inheritdoc
     */
    protected function getRemoteProfile(Token $token)
    {
        return $this->getOauthProvider()->get("me",$token->token);
    }

    /**
     * Get the redirect URI.
     *      Azure AD doesn't support redirect_uri with query string - so we need to encode query in the base url
     * @return string
     */
    public function getRedirectUri()
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
}