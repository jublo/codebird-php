codebird-php - changelog
========================

2.6.1 (not yet released)

2.6.0 (2014-10-12)
+ #67 Don't require cURL, allow stream connections too
+ Use default timeout
+ #69 Add composer/installers to allow install path changes
- Regression: Codebird::getApiMethods removed accidentally in 2.5.0
+ #66 Allow remote media uploads
- #81 CURLOPT_TIMEOUT_MS and CURLOPT_CONNECTTIMEOUT_MS errors on PHP 5.3
- #83 Use POST for users/lookup and statuses/lookup, params may get too long for GET
+ Update README to reflect new process for uploading single/multiple media, see #78

2.5.0 (2014-06-20)
+ Add section about cacert.pem to README
+ Option to set cURL timeout
+ #42 Allow to get the supported API methods as array
+ #48 Update composer file to jublonet path
+ Update cacert.pem root certificates file
+ #58 support for GET statuses/retweeters/ids
+ #53 Better Proxy HTTP status detection
- #62 Places & Geo API call issue
+ #33 Support internal API methods
+ #59 Support all known API methods
+ Reset bearer token when requesting a new token
+ #61 Return rate limit details with each API call
+ #60 Support uploading multiple media
- #63 Return rate-limiting info only if contained in response
- #57 Don't require consumer key if bearer token is already known

2.4.1 (2013-06-23)
+ #26 Stringify null and boolean parameters
+ Validate Twitter SSL certificate for oauth2/token method

2.4.0 (2013-06-15)
+ Add contributing guidelines
+ rfe #21 JSON return format
+ Support HTTP proxy replies
+ Validate Twitter SSL certificate
+ #23 Readme: Fix authentication sample

2.3.6 (2013-05-12)
+ Add backslash to stdClass construction, due to namespace

2.3.5 (2013-04-30)
+ Fix fatal error:  Class 'Codebird\Exception' not found

2.3.4 (2013-04-28)
+ Fix namespace not properly cased

2.3.3 (2013-04-26)
+ Detect API error responses as XML, see #1
+ OAuth: add support for force_login, screen_name parameters, see #14
+ Add namespace

2.3.2 (2013-04-09)
+ Use protected keyword for methods that may be overriden in extended classes

2.3.1 (2013-03-23)
- Re-remove statuses/sample. It's streaming API, which Codebird doesn't currently support
+ Don't send multipart POST to non-multipart methods, fix issue #8
+ Remove auto-added backslashes from parsed API method parameters if magic quotes are on, fix #7

2.3.0 (2013-03-19)
+ Update README with info about how to get details of the current user
+ Clarify that the consumer secret and access token secret are not the same
+ Readme: Clarify authentication tokens
+ Add README section about rate-limiting
+ Add README section about cursored results
+ Add OAuth2 application-only auth
- Drop support for statuses/public_timeline
- Drop support for v1 API
+ add statuses/sample, friendships/no_retweets/ids methods
+ Add Changelog

2.2.3 (2012-12-03)
+ Fix wrong assignment operator in _detectMethod for multi HTTP method endpoints
+ Add documentation about $params in _detectMethod
+ Add new methods now available in 1.1
+ Add friends/list, followers/list API methods

2.2.2 (2012-10-17)
+ Add profile banner methods
+ Drop separate media upload method as noted at https://dev.twitter.com/docs/api/1.1/post/statuses/update_with_media
+ Fix wrong Exception
+ add support for old endpoints
+ Update endpoints to 1.1 endpoints

2.2.1 (2012-09-17)
+ Update to Twitter API 1.1

2.2.0 (2012-07-08)
+ Add support for users/profile_image/:screen_name
- drop support for string return format

2.1 (2012-07-07)
+ Add documentation in readme
+ make OAuth consumer key and secret static
+ Highlight the 'uploading files to Twitter' docs
+ Return HTTP status with array return_format

2.0 (2012-07-06)
+ Complete rewrite. Now supports media uploads.
+ Support API methods with templated variables (like statuses/show/:id)
+ Add documentation

1.2 (2012-07-04)
+ Apply PEAR coding standards
+ Use self instead of class name

1.1 (2011-07-08)
+ Fixed: Removed ; sign.
+ Added: Detect parameter-less messages (such as, error messages).
+ Changed: Oauth_Authorize should return the URL instead of redirecting there.
+ Added: Ability to provide parameters to oauth/request_token.
+ Changed: Use api.twitter.com as OAuth hostname.
+ Added: Allow oauth/access_token to have parameters.
+ Changed: Don't rely on intval() for the large tweet id's that Snowflake is generating nowadays.
+ Fixed: Decode empty JSON arrays [] properly.
