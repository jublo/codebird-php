codebird-php
============
*A simple wrapper for the Twitter API*

Copyright (C) 2010-2012 J.M. <me@mynetx.net>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.


This is the PHP version of the Codebird library.
It was forked from the JScript version.
Please enable the CURL and OPENSSL extensions in your PHP environment.

Usage example
-------------

```php
require_once ('codebird.php');
Codebird::setConsumerKey('YOURKEY', 'YOURSECRET'); // static, see 'Using multiple Codebird instances'

$cb = Codebird::getInstance();
```

You may either set the OAuth token and secret, if you already have them:
```php
$cb->setToken('YOURTOKEN', 'YOURTOKENSECRET');
```

Or you authenticate, like this:

```php
session_start();

if (! isset($_GET['oauth_verifier'])) {
    // gets a request token
    $reply = $cb->oauth_requestToken(array(
        'oauth_callback' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
    ));

    // stores it
    $cb->setToken($reply->oauth_token, $reply->oauth_token_secret);
    $_SESSION['oauth_token'] = $reply->oauth_token;
    $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;

    // gets the authorize screen URL
    $auth_url = $cb->oauth_authorize();
    header('Location: ' . $auth_url);
    die();

} else {
    // gets the access token
    $cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
    $reply = $cb->oauth_accessToken(array(
        'oauth_verifier' => $_GET['oauth_verifier']
    ));
    $_SESSION['oauth_token'] = $reply->oauth_token;
    $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;
}
```

When you have an access token, calling the API is simple:

```php
$reply = (array) $cb->statuses_homeTimeline();
print_r($reply);
```

Tweeting is as easy as this:

```php
$reply = $cb->statuses_update('status=Whohoo, I just tweeted!');
```

For more complex parameters (see the [Twitter API documentation](https://dev.twitter.com/)),
giving all parameters in an array is supported, too:

```php
$params = array(
    'screen_name' => 'mynetx'
);
$reply = $cb->users_show($params);
```

When **uploading files to Twitter**, the array syntax is obligatory:

```php
$params = array(
    'status' => 'Look at this crazy cat! #lolcats',
    'media[]' => '/home/mynetx/lolcats.jpg'
);
$reply = $cb->statuses_updateWithMedia($params);
```

Mapping API methods to Codebird function calls
----------------------------------------------

As you can see from the last example, there is a general way how Twitter’s API methods
map to Codebird function calls. The general rules are:

1. For each slash in a Twitter API method, use an underscore in the Codebird function.

    Example: ```statuses/update``` maps to ```Codebird::statuses_update()```.

2. For each underscore in a Twitter API method, use camelCase in the Codebird function.

    Example: ```statuses/home_timeline``` maps to ```Codebird::statuses_homeTimeline()```.

3. For each parameter template in method, use UPPERCASE in the Codebird function.
    Also don’t forget to include the parameter in your parameter list.

    Examples:
    - ```statuses/show/:id``` maps to ```Codebird::statuses_show_ID('id=12345')```.
    - ```users/profile_image/:screen_name``` maps to
      ```Codebird::users_profileImage_SCREEN_NAME('screen_name=mynetx')```.

HTTP methods (GET, POST, DELETE etc.)
-------------------------------------

Never care about which HTTP method (verb) to use when calling a Twitter API.
Codebird is intelligent enough to find out on its own.

Response codes
--------------

The HTTP response code that the API gave is included in any return values.
You can find it within the return object’s ```httpstatus``` property.

Return formats
--------------
The default return format for API calls is a PHP object.
For API methods returning multiple data (like ```statuses/home_timeline```),
you should cast the reply to array, like this:

```php
$reply = $cb->statuses_homeTimeline();
$data = (array) $reply;
```

Upon your choice, you may also get PHP arrays directly:

```php
$cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
```

Using multiple Codebird instances
---------------------------------

By default, Codebird works with just one instance. This programming paradigma is
called a *singleton*.

Getting the main Codebird object is done like this:

```php
$cb = Codebird::getInstance();
```

If you need to run requests to the Twitter API for multiple users at once,
Codebird supports this as well. Instead of getting the instance like shown above,
create a new object:

```php
$cb1 = new Codebird;
$cb2 = new Codebird;
```

Please note that your OAuth consumer key and secret is shared within
multiple Codebird instances, while the OAuth request and access tokens with their
secrets are *not* shared.

Specialities
============

Accessing a user’s profile image
--------------------------------

The Twitter API usually contains data in either JSON or XML. However, the
templated method ```users/profile_image/:screen_name``` uses a HTTP 302 redirect
to send you to the requested image file URL.

Codebird intercepts this HTTP redirect and extracts the profile image URL instead.
Thus, the following API call:

```php
$reply = $cb->users_profileImage_SCREEN_NAME('screen_name=mynetx&size=mini');
```

returns an object with the following contents:
```
stdClass Object
(
    [profile_image_url_https] => https://si0.twimg.com/profile_images/1417135246/Blue_Purple.96_mini.png
    [httpstatus] => 302
)
```

You can find out how to build the Codebird method name, in the section
‘Mapping API methods to Codebird function calls.’

How Do I…?
==========

…get user ID, screen name and more details about the current user?
------------------------------------------------------------------

When the user returns from the authentication screen, you need to trade
the obtained request token for an access token, using the OAuth verifier.
As discussed in the section ‘Usage example,’ you use a call to 
```oauth/access_token``` to do that.

The API reply to this method call tells you details about the user that just logged in.
These details contain the **user ID** and the **screen name.**

Take a look at the returned data as follows:

```
stdClass Object
(
    [oauth_token] => 14648265-rPn8EJwfB**********************
    [oauth_token_secret] => agvf3L3**************************
    [user_id] => 14648265
    [screen_name] => mynetx
    [httpstatus] => 200
) 
```

If you need to get more details, such as the user’s latest tweet, 
you should fetch the complete User Entity.  The simplest way to get the 
user entity of the currently authenticated user is to use the 
```account/verify_credentials``` API method.  In Codebird, it works like this:

```php
$reply = $cb->account_verifyCredentials();
print_r($reply);
```

I suggest to cache the User Entity after obtaining it, as the 
```account/verify_credentials``` method is rate-limited by 15 calls per 15 minutes. 
