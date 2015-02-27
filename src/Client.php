<?php namespace Maer\Oauth2Simple\Client;

use Exception;
use League\OAuth2\Client\Exception\IDPException;
use League\OAuth2\Client\Provider\AbstractProvider;

class Client implements ClientInterface
{
    const MISSING_CONFIG_PROVIDER = 1;
    const INVALID_PROVIDER        = 2;
    const INVALID_CODE            = 3;
    const INVALID_STATE           = 4;
    const OAUTH_EXCEPTION         = 5;
    const UNAUTHORIZED            = 6;

    protected $config;
    protected $client;
    protected $error;
    protected $errorCode;
    protected $token;
    
    public function __construct(array $config)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $this->config = $config;
    }

    public function getProvider()
    {
        if (is_null($this->client)) {

            $provider = $this->config('provider');

            if (!$provider) {
                // No provider was configured. We can't have that!
                $this->setErrorCode(self::MISSING_CONFIG_PROVIDER);
                $this->setError('No provider found in the configuration');
                return false;
            }

            /*
             * Check if the provider is built in
             * ---------------------------------
             */
            // Load the available providers
            $providers    = include __DIR__ . '/providers.php';
            $providerPath = array_key_exists(strtolower($provider), $providers)? $providers[strtolower($provider)] : null;
            $client       = null;

            if ($providerPath) {
                return $this->client = new $providerPath($this->config);
            }

            /*
             * Check if there is a custom provider
             * ---------------------------------
             */
            $provider = '\\' . ltrim($provider, '\\');
            if (class_exists($provider)) {
                $client = new $provider($this->config);
            }

            if (!$client || !$client instanceof AbstractProvider) {
                // The custom client isn't extending AbstractProvider so we have no
                // idea if this is a oauth provider or how to use it.
                $this->setErrorCode(self::INVALID_PROVIDER);
                $this->setError('Custom providers need to be extending League\OAuth2\Client\Provider\AbstractProvider');
                return false;
            }

            return $this->client = $client;
        }

        return $this->client;
    }

    public function getAuthorizationUrl()
    {
        $url = $this->getProvider()->getAuthorizationUrl();
        $this->setState();
        return $url;
    }

    public function authorize()
    {
        $code = isset($_GET['code'])? $_GET['code'] : null;

        if (empty($code)) {
            $this->setErrorCode(self::INVALID_CODE);
            $this->setError('Invalid code in the callback');
            return false;
        }

        if (!$this->validateState()) {
            $this->setErrorCode(self::INVALID_STATE);
            $this->setError('Invalid state');
            return false;
        }

        $client = $this->getProvider();
        
        try {
            $token  = $client->getAccessToken('authorization_code', ['code' => $code]);
        } catch(IDPException $e) {
            $this->setErrorCode(self::OAUTH_EXCEPTION);
            $this->setError('Exception occurred when fetching token: ' . $e->getMessage());
            return false;
        }
    
        try {
            $user = $client->getUserDetails($token);
        } catch (Exception $e) {
            $this->setErrorCode(self::OAUTH_EXCEPTION);
            $this->setError('Exception occurred when fetching user data: ' . $e->getMessage());
            return false;
        }

        // Check if the user has access
        $accessControl = new AccessControl;
        $emailAllow = $this->config('emailAllow', []);
        $emailDeny  = $this->config('emailDeny', []);
        
        if ($emailDeny || $emailAllow) {

            if (!$accessControl->isEmailAllowed($emailAllow, $emailDeny, $user->email)) {
                $this->setErrorCode(self::UNAUTHORIZED);
                $this->setError('The user is not allowed access.');
                return false;
            }

        }

        $this->setToken($token);
        return $user;

    }

    protected function setToken($token)
    {
        $this->token = $token;
    }

    public function getToken($token)
    {
        return $this->token;
    }

    protected function setState()
    {
        $_SESSION['oauth2state'] = $this->getProvider()->state;
    }

    protected function setError($error)
    {
        $this->error = $error;
    }

    public function getError()
    {
        return $this->error;
    }

    protected function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    protected function validateState()
    {
        return (!empty($_GET['state']) && $_GET['state'] === $_SESSION['oauth2state']);
    }

    /**
     * Get a config value from the config array
     * 
     * @param  string   $key        Name of the config parameter
     * @param  mixed    $default    If key doesn't exist, return this
     * @return mixed
     */
    protected function config($key, $default = null)
    {
        return array_key_exists($key, $this->config)? $this->config[$key] : $default;
    }

}