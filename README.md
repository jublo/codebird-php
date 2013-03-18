codebird-php
============
*A Twitter library in PHP.*

Copyright (C) 2010-2013 J.M. <me@mynetx.net>

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

1. Authentication
-----------------

To authenticate your API requests on behalf of a certain Twitter user
(following OAuth 1.0a), take a look at these steps:

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
    // store the authenticated token, which may be different from the request token (!)
    $_SESSION['oauth_token'] = $reply->oauth_token;
    $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;
}
```

### 1.1. Application-only auth

Some API methods also support authenticating on a per-application level.
This is useful for getting data that are not directly related to a specific
Twitter user, but generic to the Twitter ecosystem (such as ```search/tweets```).

To obtain an app-only bearer token, call the appropriate API:

```php
$reply = $cb->oauth2_token();
$bearer_token = $reply->access_token;
```

I strongly recommend that you store the obtained bearer token in your database.
There is no need to re-obtain the token with each page load, as it becomes invalid
only when you call the ```oauth2/invalidate_token``` method.

If you already have your token, tell Codebird to use it:
```php
Codebird::setBearerToken('YOURBEARERTOKEN');
```

For sending an API request with app-only auth, see the ‘Usage examples’ section.


2. Usage examples
-----------------

When you have an access token, calling the API is simple:

```php
$cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']); // see above

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

### Requests with app-only auth

To send API requests without an access token for a user (app-only auth),
add a second parameter to your method call, like this:

```php
$reply = $cb->search_tweets('q=Twitter', true);
```

Bear in mind that not all API methods support application-only auth.

3. Mapping API methods to Codebird function calls
-------------------------------------------------

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

4. HTTP methods (GET, POST, DELETE etc.)
----------------------------------------

Never care about which HTTP method (verb) to use when calling a Twitter API.
Codebird is intelligent enough to find out on its own.

5. Response codes
-----------------

The HTTP response code that the API gave is included in any return values.
You can find it within the return object’s ```httpstatus``` property.

### 5.1 Dealing with rate-limits

Basically, Codebird leaves it up to you to handle Twitter’s rate limit.  
The library returns the response HTTP status code, so you can detect rate limits.

I suggest you to check if the ```$reply->httpstatus``` property is ```400``` 
and check with the Twitter API to find out if you are currently being 
rate-limited. 
See the [Rate Limiting FAQ](https://dev.twitter.com/docs/rate-limiting-faq) 
for more information.

6. Return formats
-----------------
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

Support for getting a SimpleXML object is planned.

7. Using multiple Codebird instances
------------------------------------

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

How Do I…?
==========

…access a user’s profile image?
-------------------------------

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

…walk through cursored results?
-------------------------------

The Twitter REST API utilizes a technique called ‘cursoring’ to paginate 
large result sets. Cursoring separates results into pages of no more than 
5000 results at a time, and provides a means to move backwards and 
forwards through these pages. 

Here is how you can walk through cursored results with Codebird.

1. Get the first result set of a cursored method:
```php
$result1 = $cb->followers_list();
```

2. To navigate forth, take the ```next_cursor_str```:
```php
$nextCursor = $result1->next_cursor_str;
```

3. If ```$nextCursor``` is not 0, use this cursor to request the next result page:
```php
    if ($nextCursor > 0) {
        $result2 = $cb->followers_list('cursor=' . $nextCursor);
    }
```

To navigate back instead of forth, use the field ```$resultX->previous_cursor_str``` 
instead of ```next_cursor_str```.

It might make sense to use the cursors in a loop.  Watch out, though, 
not to send more than the allowed number of requests to ```followers/list``` 
per rate-limit timeframe, or else you will hit your rate-limit.
