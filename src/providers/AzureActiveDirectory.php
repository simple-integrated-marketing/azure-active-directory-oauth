<?php

namespace simpleteam\azureactivedirectoryoauth\providers;

use Craft;
use dukt\social\base\LoginProvider;
use dukt\social\models\Token;
use dukt\social\helpers\SocialHelper;
use League\OAuth2\Client\Provider\GenericProvider;

class AzureActiveDirectory extends LoginProvider
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'Microsoft Entra ID';
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
        return 'https://entra.microsoft.com';
    }

    /**
     * @inheritdoc
     */
    public function getProfile(Token $token)
    {
        $remoteProfile = $this->getRemoteProfile($token);
        return [
            'id' => $remoteProfile['id'] ?? null,
            'email' => $remoteProfile['userPrincipalName'] ?? null,
            'givenName' => $remoteProfile['givenName'] ?? null,
            'full' => $remoteProfile,
        ];
    }

    // Protected Methods
    // =========================================================================

    /**
     * Returns the login provider's OAuth provider.
     *
     * @return \League\OAuth2\Client\Provider\GenericProvider
     */
    public function getOauthProvider()
    {
        $providerInfos = $this->getOauthProviderConfig();
        $tenant = $providerInfos['options']['tenantId'] ?? 'common';

        $config = [
            'clientId' => $providerInfos['options']['clientId'] ?? '',
            'clientSecret' => $providerInfos['options']['clientSecret'] ?? '',
            'redirectUri' => $this->getRedirectUri(),
            'urlAuthorize' => "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/authorize",
            'urlAccessToken' => "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token",
            'urlResourceOwnerDetails' => 'https://graph.microsoft.com/v2.0/me',
            'scopes' => [
                'User.Read'
            ],
        ];

        return new GenericProvider($config);
    }

    /**
     * @inheritdoc
     */
    protected function getRemoteProfile(Token $token)
    {
        $provider = $this->getOauthProvider();
        
        // Get basic profile information
        $request = $provider->getAuthenticatedRequest(
            'GET',
            'https://graph.microsoft.com/v1.0/me',
            $token->token
        );
        
        $profile = $provider->getParsedResponse($request);
        
        // Get additional profile information if needed
        $request = $provider->getAuthenticatedRequest(
            'GET',
            'https://graph.microsoft.com/v1.0/me?$select=id,userPrincipalName,givenName,surname,mail,displayName,jobTitle,department,officeLocation',
            $token->token
        );
        
        return $provider->getParsedResponse($request);
    }

    /**
     * Get the redirect URI.
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
        return Craft::$app->assetManager->getPublishedUrl('@simpleteam/azureactivedirectoryoauth/icon.svg', true);
    }

    public function getDefaultUserFieldMapping(): array
    {
        return [
            'email' => 'userPrincipalName',
            'username' => 'userPrincipalName',
            'firstName' => 'givenName',
            'lastName' => 'surname',
            'displayName' => 'displayName',
            'jobTitle' => 'jobTitle',
            'department' => 'department',
            'officeLocation' => 'officeLocation'
        ];
    }
}