<?php namespace Maer\Oauth2Simple\Client;

use Exception;

class Factory
{
    protected static $instances = [];
    protected static $configs = [];

    /**
     * Send in configuration for an Oauth2 provider
     * 
     * @param  string   $name       What you want to call this connection
     * @param  array    $config     The configuration
     * @return void
     */
    public static function config($name, array $config)
    {
        self::$configs[strtolower($name)] = $config;
    }

    /**
     * Get an Oauth2 Provider
     * 
     * @param  string   $name   The connection name
     * @return Maer\Oauth2Simple\Client\Client
     */
    public static function get($name)
    {
        $key = strtolower($name);
        if (!array_key_exists($key, self::$instances)) {
            
            // Check if we have a config for this connection
            if (!array_key_exists($key, self::$configs)) {
                throw new Exception("No config for the connection '{$name}' was found.");
            }

            self::$instances[$key] = new Client(self::$configs[$key]);
        }

        return self::$instances[$key];
    }
}