<?php

/*  codebird-php
*  Copyright (C) 2011 Jo Michael <me@mynetx.net>
*
*  This program is free software: you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation, either version 3 of the License, or
*  (at your option) any later version.
*
*  This program is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  You should have received a copy of the GNU General Public License
*  along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class Codebird
{
    public static $_version = "1.1.2816.2016";
    public static $endpoint = "https://api.twitter.com/1/";
    public static $key = array();
    public static $token = array();
    public static $ReturnFormats = array('Object' => 1, 'String' => 2);
    public static $ReturnFormat = null;

    public static function Init()
    {
        Codebird::$ReturnFormat = Codebird::$ReturnFormats['Object'];
    }

    public static function Url($a)
    {
        if (is_array($a)) return array_map(array('Codebird', 'Url'), $a);
        elseif (is_scalar($a)) return str_replace(array('+', '!', '*', "'", '(',
                ')'), array(' ', '%21', '%2A', '%27', '%28', '%29'),
                rawurlencode($a));
        else  return '';
    }

    public static function SHA1($a)
    {
        return base64_encode(hash_hmac('sha1', $a, Codebird::$key['s'] . '&' . (isset
            (Codebird::$token['s']) ? Codebird::$token['s'] : ''), true));
    }

    public static function Nonce($a)
    {
        return substr(md5(microtime(true)), 0, $a);
    }

    public static function Sign($a, $b, $f)
    {
        $c = array('consumer_key' => Codebird::$key['k'], 'version' => '1.0',
            'timestamp' => time(), 'nonce' => Codebird::Nonce(6),
            'signature_method' => 'HMAC-SHA1');
        $d = array();
        foreach ($c as $e => $_) $d['oauth_' . $e] = Codebird::Url($_);
        if (isset(Codebird::$token['k'])) $d['oauth_token'] = Codebird::Url(Codebird::
                $token['k']);
        foreach ($f as $e => $_) $d[$e] = Codebird::Url($_);
        $c = '';
        ksort($d);
        foreach ($d as $e => $_) {
            $c .= $e . '=' . $_ . '&';
        }
        $f = Codebird::SHA1($a . '&' . Codebird::Url($b) . '&' . Codebird::Url(substr
            ($c, 0, -1)));
        return (($a == 'GET' ? $b . '?' : '') . $c . 'oauth_signature=' .
            Codebird::Url($f));
    }

    public static function CallApi($x, $a, $b, $c)
    {
        if ($c && !isset(Codebird::$token['k'])) return array(401, array());
        $a = substr($a, 0, 6) == 'oauth/' ? 'https://api.twitter.com/' . $a :
            Codebird::$endpoint . $a . '.json';
        $e = false;
        if ($x == 'GET') {
            $e = curl_init(Codebird::Sign('GET', $a, $b));
        }
        else {
            $e = curl_init($a);
            curl_setopt($e, CURLOPT_POST, 1);
            curl_setopt($e, CURLOPT_POSTFIELDS, Codebird::Sign('POST', $a, $b));
        }
        curl_setopt($e, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($e, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($e, CURLOPT_HEADER, 0);
        curl_setopt($e, CURLOPT_SSL_VERIFYPEER, 0);
        $g = curl_exec($e);
        $f = curl_getinfo($e, CURLINFO_HTTP_CODE);
        if (Codebird::$ReturnFormat == Codebird::$ReturnFormats['Object']) $g =
                Codebird::ParseApiReply($g);
        return array($f, $g);
    }

    public static function CallApiGet($a, $b, $c)
    {
        return Codebird::CallApi('GET', $a, $b, $c);
    }

    public static function CallApiPost($a, $b, $c)
    {
        return Codebird::CallApi('POST', $a, $b, $c);
    }

    public static function ParseApiReply($a)
    {
        $b = array();
        if (!$b = json_decode($a, true)) {
            if ($a) {
                $a = explode('&', $a);
                for ($c = 0; $c < count($a); $c++) {
                    $d = explode('=', $a[$c]);
                    $b[$d[0]] = $d[1];
                }
            }
        }
        return $b;
    }

    public static function Account_EndSession()
    {
        Codebird::$token = array();
    }

    public static function Account_RateLimitStatus()
    {
        return Codebird::CallApiGet('account/rate_limit_status', array(), true);
    }

    public static function UpdateDeliveryDevice($a)
    {
        return Codebird::CallApiPost('account/update_delivery_device', $a, true);
    }

    public static function Account_UpdateProfile($a)
    {
        return Codebird::CallApiPost('account/update_profile', $a, true);
    }

    public static function Account_UpdateProfileColors($a)
    {
        return Codebird::CallApiPost('account/update_profile_colors', $a, true);
    }

    public static function Account_UpdateProfileBackgroundImage($a)
    {
        throw new Exception('Requires OAuth with multipart POST. You know how? Tell me.');
    }

    public static function Account_UpdateProfileImage($a)
    {
        throw new Exception('Requires OAuth with multipart POST. You know how? Tell me.');
    }

    public static function Account_VerifyCredentials()
    {
        return Codebird::CallApiGet('account/verify_credentials', array('suppress_response_codes' =>
            'true'), true);
    }

    public static function Blocks_Blocking($a)
    {
        return Codebird::CallApiGet('blocks/blocking', $a, true);
    }

    public static function Blocks_Blocking_Ids($a)
    {
        return Codebird::CallApiGet('blocks/blocking/ids', $a, true);
    }

    public static function Blocks_Create($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiPost('blocks/create/' . intval($a['id']), $a, true);
    }

    public static function Blocks_Destroy($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiPost('blocks/destroy/' . intval($a['id']), $a, true);
    }

    public static function Blocks_Exists($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiGet('blocks/exists/' . intval($a['id']), $a, true);
    }

    public static function DirectMessages($a)
    {
        return Codebird::CallApiGet('direct_messages', $a, true);
    }

    public static function DirectMessages_Destroy($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiPost('direct_messages/destroy/' . intval($a['id']),
            $a, true);
    }

    public static function DirectMessages_New($a)
    {
        return Codebird::CallApiPost('direct_messages/new', $a, true);
    }

    public static function DirectMessages_Sent($a)
    {
        return Codebird::CallApiGet('direct_messages/sent', $a, true);
    }

    public static function Favorites($a)
    {
        return Codebird::CallApiGet('favorites', $a, true);
    }

    public static function Favorites_Create($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiPost('favorites/create/' . intval($a['id']), $a, true);
    }

    public static function Favorites_Destroy($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiPost('favorites/destroy/' . intval($a['id']), $a, true);
    }
    public static function Followers_Ids($a)
    {
        return Codebird::CallApiGet('followers/ids', $a, true);
    }

    public static function Friends_Ids($a)
    {
        return Codebird::CallApiGet('friends/ids', $a, true);
    }

    public static function Friendships_Create($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiPost('friendships/create/' . intval($a['id']), $a, true);
    }

    public static function Friendships_Destroy($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiPost('friendships/destroy/' . intval($a['id']),
            $a, true);
    }

    public static function Friendships_Exists($a)
    {
        return Codebird::CallApiGet('friendships/exists', $a, true);
    }

    public static function Friendships_Show($a)
    {
        return Codebird::CallApiGet('friendships/show', $a, true);
    }

    public static function Geo_Id($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiGet('geo/id/' . intval($a['id']), $a, true);
    }

    public static function Geo_NearbyPlaces($a)
    {
        return Codebird::CallApiGet('geo/nearby_places', $a, true);
    }

    public static function Geo_Place($a)
    {
        return Codebird::CallApiPost('geo/place', $a, true);
    }

    public static function Geo_ReverseGeocode($a)
    {
        return Codebird::CallApiGet('geo/reverse_geocode', $a, true);
    }

    public static function Geo_Search($a)
    {
        return Codebird::CallApiGet('geo/search', $a, true);
    }

    public static function Geo_SimilarPlaces($a)
    {
        return Codebird::CallApiGet('geo/similar_places', $a, true);
    }

    public static function Help_Test()
    {
        return Codebird::CallApiGet('help/test', array(), false);
    }

    public static function Legal_Privacy()
    {
        return Codebird::CallApiGet('legal/privacy', array(), false);
    }

    public static function Legal_Tos()
    {
        return Codebird::CallApiGet('legal/tos', array(), false);
    }

    public static function Notifications_Follow($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiPost('notifications/follow/' . intval($a['id']),
            $a, true);
    }

    public static function Notifications_Leave($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiPost('notifications/leave/' . intval($a['id']),
            $a, true);
    }

    public static function Oauth_RequestToken()
    {
        return Codebird::CallApiGet('oauth/request_token', array(), false);
    }

    public static function Oauth_Authorize()
    {
        header('Location: https://twitter.com/oauth/authorize?oauth_token=' .
            Codebird::Url(Codebird::$token['k']));
        die();
    }

    public static function Oauth_AccessToken()
    {
        return Codebird::CallApiPost('oauth/access_token', array(), true);
    }

    public static function ReportSpam($a)
    {
        return Codebird::CallApiPost('report_spam', $a, true);
    }

    public static function SavedSearches($a)
    {
        return Codebird::CallApiGet('saved_searches', $a, true);
    }

    public static function SavedSearches_Create($a)
    {
        return Codebird::CallApiPost('saved_searches/create', $a, true);
    }

    public static function SavedSearches_Destroy($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiPost('saved_searches/destroy/' . intval($a['id']),
            array(), true);
    }

    public static function SavedSearches_Show($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiPost('saved_searches/show/' . intval($a['id']),
            $a, true);
    }

    public static function Statuses_Destroy($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiPost('statuses/destroy/' . intval($a['id']),
            array(), true);
    }

    public static function Statuses_Followers($a)
    {
        return Codebird::CallApiGet('statuses/followers', $a, true);
    }

    public static function Statuses_Friends($a)
    {
        return Codebird::CallApiGet('statuses/friends', $a, true);
    }

    public static function Statuses_FriendsTimeline($a)
    {
        return Codebird::CallApiGet('statuses/friends_timeline', $a, true);
    }

    public static function Statuses_HomeTimeline($a)
    {
        return Codebird::CallApiGet('statuses/home_timeline', $a, true);
    }

    public static function Statuses_RetweetedBy($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiGet('statuses/' . intval($a['id']) .
            '/retweeted_by', $a, true);
    }

    public static function Statuses_RetweetedBy_Ids($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiGet('statuses/' . intval($a['id']) .
            '/retweeted_by/ids', $a, true);
    }

    public static function Statuses_Mentions($a)
    {
        return Codebird::CallApiGet('statuses/mentions', $a, true);
    }

    public static $Statuses_PublicTimelineCache = array('timestamp' => false,
        'data' => false);

    public static function Statuses_PublicTimeline()
    {
        if (Codebird::$Statuses_PublicTimelineCache['timestamp'] && Codebird::$Statuses_PublicTimelineCache['timestamp'] +
            60 > time()) return array(200, Codebird::$Statuses_PublicTimelineCache['data']);
        $arrReply = Codebird::CallApiGet('statuses/public_timeline', array(), true);
        if ($arrReply[0] == 200) {
            Codebird::$Statuses_PublicTimelineCache = array('timestamp' => time
                (), 'data' => $arrReply[1]);
        }
        return $arrReply;
    }

    public static function Statuses_Retweet($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiPost('statuses/retweet/' . intval($a['id']),
            array(), true);
    }

    public static function Statuses_Retweets($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiGet('statuses/retweets/' . intval($a['id']), $a, true);
    }

    public static function Statuses_RetweetedByMe($a)
    {
        return Codebird::CallApiGet('statuses/retweeted_by_me', $a, true);
    }

    public static function Statuses_RetweetedToMe($a)
    {
        return Codebird::CallApiGet('statuses/retweeted_to_me', $a, true);
    }

    public static function Statuses_RetweetsOfMe($a)
    {
        return Codebird::CallApiGet('statuses/retweets_of_me', $a, true);
    }

    public static function Statuses_Show($a)
    {
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiGet('statuses/show/' . intval($a['id']), $a, true);
    }

    public static function Statuses_Update($a)
    {
        return Codebird::CallApiPost('statuses/update', $a, true);
    }

    public static function Statuses_UserTimeline($a)
    {
        return Codebird::CallApiGet('statuses/user_timeline', $a, true);
    }

    public static function Trends($a)
    {
        if (!isset($a['woeid'])) throw new Exception('Missing parameter woeid.');
        return Codebird::CallApiGet('trends/' . intval($a['id']), $a, true);
    }

    public static function Trends_Available($a)
    {
        return Codebird::CallApiGet('trends/available', $a, false);
    }

    public static function Trends_Current($a)
    {
        return Codebird::CallApiGet('trends/current', $a, false);
    }

    public static function Trends_Daily($a)
    {
        return Codebird::CallApiGet('trends/daily', $a, false);
    }

    public static function Trends_Weekly($a)
    {
        return Codebird::CallApiGet('trends/weekly', $a, false);
    }

    public static function User_ListId_CreateAll($a)
    {
        if (!isset($a['user'])) throw new Exception('Missing parameter user.');
        if (!isset($a['list_id'])) throw new Exception('Missing parameter list_id.');
        return Codebird::CallApiPost($a['user'] . '/' . $a['list_id'] .
            '/create_all', $a, true);
    }

    public static function User_ListId_Members($a)
    {
        if (!isset($a['user'])) throw new Exception('Missing parameter user.');
        if (!isset($a['list_id'])) throw new Exception('Missing parameter list_id.');
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiGet($a['user'] . '/' . $a['list_id'] .
            '/members/' . $a['id'], $a, true);
    }

    public static function User_ListId_Members_Delete($a)
    {
        if (!isset($a['user'])) throw new Exception('Missing parameter user.');
        if (!isset($a['list_id'])) throw new Exception('Missing parameter list_id.');
        $a['_method'] = 'DELETE';
        return Codebird::CallApiPost($a['user'] . '/' . $a['list_id'] .
            '/members', $a, true);
    }

    public static function User_ListId_Members_Get($a)
    {
        if (!isset($a['user'])) throw new Exception('Missing parameter user.');
        if (!isset($a['list_id'])) throw new Exception('Missing parameter list_id.');
        return Codebird::CallApiGet($a['user'] . '/' . $a['list_id'] .
            '/members', $a, true);
    }

    public static function User_ListId_Members_Post($a)
    {
        if (!isset($a['user'])) throw new Exception('Missing parameter user.');
        if (!isset($a['list_id'])) throw new Exception('Missing parameter list_id.');
        return Codebird::CallApiPost($a['user'] . '/' . $a['list_id'] .
            '/members', $a, true);
    }

    public static function User_ListId_Subscribers($a)
    {
        if (!isset($a['user'])) throw new Exception('Missing parameter user.');
        if (!isset($a['list_id'])) throw new Exception('Missing parameter list_id.');
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiGet($a['user'] . '/' . $a['list_id'] .
            '/subscribers/' . $a['id'], $a, true);
    }

    public static function User_ListId_Subscribers_Delete($a)
    {
        if (!isset($a['user'])) throw new Exception('Missing parameter user.');
        if (!isset($a['list_id'])) throw new Exception('Missing parameter list_id.');
        $a['_method'] = 'DELETE';
        return Codebird::CallApiPost($a['user'] . '/' . $a['list_id'] .
            '/subscribers', $a, true);
    }

    public static function User_ListId_Subscribers_Get($a)
    {
        if (!isset($a['user'])) throw new Exception('Missing parameter user.');
        if (!isset($a['list_id'])) throw new Exception('Missing parameter list_id.');
        return Codebird::CallApiGet($a['user'] . '/' . $a['list_id'] .
            '/subscribers', $a, true);
    }

    public static function User_ListId_Subscribers_Post($a)
    {
        if (!isset($a['user'])) throw new Exception('Missing parameter user.');
        if (!isset($a['list_id'])) throw new Exception('Missing parameter list_id.');
        return Codebird::CallApiPost($a['user'] . '/' . $a['list_id'] .
            '/subscribers', $a, true);
    }

    public static function User_Lists_Get($a)
    {
        if (!isset($a['user'])) throw new Exception('Missing parameter user.');
        return Codebird::CallApiGet($a['user'] . '/lists', $a, true);
    }

    public static function User_Lists_Post($a)
    {
        if (!isset($a['user'])) throw new Exception('Missing parameter user.');
        return Codebird::CallApiPost($a['user'] . '/lists', $a, true);
    }

    public static function User_Lists_Id_Delete($a)
    {
        if (!isset($a['user'])) throw new Exception('Missing parameter user.');
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        $a['_method'] = 'DELETE';
        return Codebird::CallApiPost($a['user'] . '/lists/' . $a['id'], $a, true);
    }

    public static function User_Lists_Id_Get($a)
    {
        if (!isset($a['user'])) throw new Exception('Missing parameter user.');
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiGet($a['user'] . '/lists/' . $a['id'], $a, true);
    }

    public static function User_Lists_Id_Post($a)
    {
        if (!isset($a['user'])) throw new Exception('Missing parameter user.');
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiPost($a['user'] . '/lists/' . $a['id'], $a, true);
    }

    public static function User_Lists_Id_Statuses($a)
    {
        if (!isset($a['user'])) throw new Exception('Missing parameter user.');
        if (!isset($a['id'])) throw new Exception('Missing parameter id.');
        return Codebird::CallApiGet($a['user'] . '/lists/' . $a['id'] .
            '/statuses', $a, true);
    }

    public static function User_Lists_Memberships($a)
    {
        if (!isset($a['user'])) throw new Exception('Missing parameter user.');
        return Codebird::CallApiGet($a['user'] . '/lists/memberships', $a, true);
    }

    public static function User_Lists_Subscriptions($a)
    {
        if (!isset($a['user'])) throw new Exception('Missing parameter user.');
        return Codebird::CallApiGet($a['user'] . '/lists/subscriptions', $a, true);
    }

    public static function Users_Lookup($a)
    {
        return Codebird::CallApiGet('users/lookup', $a, true);
    }

    public static function Users_ProfileImage($a)
    {
        if (!isset($a['screen_name'])) throw new Exception('Missing parameter screen_name.');
        return Codebird::CallApiGet('users/profile_image/' . $a['screen_name'], $a, true);
    }

    public static function Users_Search($a)
    {
        return Codebird::CallApiGet('users/search', $a, true);
    }

    public static function Users_Show($a)
    {
        return Codebird::CallApiGet('users/show', $a, true);
    }

    public static function Users_Suggestions($a)
    {
        return Codebird::CallApiGet('users/suggestions', $a, true);
    }

    public static function Users_Suggestions_Slug($a)
    {
        if (!isset($a['slug'])) throw new Exception('Missing parameter slug.');
        return Codebird::CallApiGet('users/suggestions/' . $a['slug'], $a, true);
    }
}
Codebird::Init();

?>