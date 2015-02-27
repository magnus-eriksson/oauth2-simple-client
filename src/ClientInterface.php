<?php namespace Maer\Oauth2Simple\Client;

interface ClientInterface
{
    /**
     * Create a new instance for Oauth2.
     *
     * @example new Maer\Oauth2Simple\Client\Client([
     *              'clientId'       => 'XXXXXXXX',
     *              'clientSecret'   => 'XXXXXXXX',
     *              'redirectUri'    => 'https://your-registered-redirect-uri/',
     *              'scopes'         => ['email', '...', '...'],
     *              'provider'       => 'google', // or write the full namespace to a custom provider,
     *              'emailAllow'     => ['foo@bar.com', 'bar.com', ...], # Optional
     *              'emailDeny'      => ['evil@bar.com', 'evil.com', ...] # Optional
     *          ]);
     * 
     * @param array $config     The provider config
     */
    public function __construct(array $config);

    /**
     * Get the provider instance
     * 
     * @return League\OAuth2\Client\Provider\AbstractProvider
     */
    public function getProvider();

    public function getAuthorizationUrl();

    public function authorize();

    public function getToken($token);

    public function getError();

    public function getErrorCode();

}