# Oauth2 Simple Client

[![Build Status](https://travis-ci.org/magnus-eriksson/oauth2-simple-client.svg?branch=master)](https://travis-ci.org/magnus-eriksson/oauth2-simple-client)


Wrapper for [league/oauth2-client](https://github.com/thephpleague/oauth2-client) with a simplified API and possibility to black/whitelist e-mail addresses/domains.

It is framework agnostic so you can just plug and play. 


## Install
You need composer to make this work, since this package is dependent on league/oauth2-client. I guess you could install that by hand as well, but since that package is dependent on other packages, you're setting yourself up for failure. ;-)

Add the package:

    composer require maer/oauth2-simple-client 0.1.*

Make sure to include the composer-generated autoloader:

    <?php
    require_once 'path-to/vendor/autoload.php';

## Usage

### The factory way

#### Config
Start by adding the configuration file. Let's use Google as example:

    Maer\Oauth2Simple\Client\Factory::config('my-google', [
        'clientId'       => 'XXXXXXXX',
        'clientSecret'   => 'XXXXXXXX',
        'redirectUri'    => 'https://example.com/callback/',
        'scopes'         => ['email'],
        'provider'       => 'google',
        'emailAllow'     => ['foo@bar.com', '@example.com'], # Optional
        'emailDeny'      => ['evil@example.com', '@evil.com'] # Optional
    ]);

The "provider" parameter can be any of the built in providers in "league/oauth2-client". You can head over to that repository to get more information. This package is using the latest 0.9.*-version.
It can also be the name of a custom provider, more about those, go to the "league/oauth2-client".
If it is a custom provider, enter the full namespace to that class, like: `"provider" : "Namespace\To\Provider\MyProvider"`

This will only save the configuration in the factory under the name "my-google". It will not make any instances or connect to anything so feel free to do this in your bootstrap or when ever. As long as it is before you try to get a connection.


#### Connect

Get a configured connection:

    $provider = Maer\Oauth2Simple\Client\Factory::get('my-google');

Get the authorization URL: 

    <a href="<?= $provider->getAuthorizationUrl()?>">Log in</a>

On the callback page `example.com/callback/`:

    $user = $provider->authorize();

If the user was successfully autorized, you will get a user object back, otherwise it will return "false". Then you can check why by looking at the error message:
    
    # For a human readable message
    $errorMessage = $provider->getError();

    # For an error code (check the constants in `Maer\Oauth2Simple\Client\Client`)
    $errorCode = $provider->getErrorCode();

If you need the token:
        
    $token = $provider->getToken();

If you want to do something that league/oauth2-client supports but isn't added to this wrapper, you can get the original provider:

    $leaguesProvider = $provider->getProvider();


### The manual way

    $provider = Maer\Oauth2Simple\Client\Client([
        'clientId'       => 'XXXXXXXX',
        'clientSecret'   => 'XXXXXXXX',
        'redirectUri'    => 'https://example.com/callback/',
        'scopes'         => ['email'],
        'provider'       => 'google',
        'emailAllow'     => ['foo@bar.com', '@example.com'], # Optional
        'emailDeny'      => ['evil@example.com', '@evil.com'] # Optional
    ]);

Now it's up to you to save the `$provider` instance. Otherwise it's just like above.


## E-mail allow/deny config

Maby we should talk about the allow- and deny lists. It is basically the reason I made this wrapper. :)
With this you can decide who is or isn't allowed to authenticate/use your app depending on their e-mail address. This does require the e-mail to be returned from the provider so make sure you add the "email" in the scope. I will add the possibility to use oauthId's and other paramaeters, but started with e-mail since that was what I needed when I built this.

The emailAllow and emailDeny:

    'emailAllow'     => ['foo@bar.com', '@example.com']

This will only allow the user with the e-mail address `foo@bar.com` or any user having an e-mail address on the `@example.com` domain. If this array is empty, or not provided at all, it will be counted as everyone is allowed.

    'emailDeny'      => ['evil@example.com', '@evil.com']

This will deny the user with the e-mail `evil@example.com` even if the domain `@example.com` is allowed. No user from `@evil.com` is allowed at all. This is kind of a bad example, since @evil.com would have been denied anyway, since we have an allow-list. Denying a domain only makes sence if you don't have an allow list (which as I stated previously is regarded as everyone is allowed).