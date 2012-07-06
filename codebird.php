<?php

/**
 * codebird-php
 *  Copyright (C) 2010-2012 J.M. <me@mynetx.net>
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
    public static $_version = "1.2.3006.1609";
    public static $endpoint = "https://api.twitter.com/1/";
    public static $endpoint_oauth = "https://api.twitter.com/";
    public static $endpoint_upload = "https://upload.twitter.com/1/";
    public static $key = array('k' => '', 's' => '');
    public static $token = array('k' => '', 's' => '');
    public static $ReturnFormats = array('Object' => 1, 'String' => 2);
    public static $ReturnFormat = null;
    
    public static function Init()
    {
        self::$ReturnFormat = self::$ReturnFormats['Object'];
    }
    
    public static function Url($a)
    {
        if (is_array($a)) {
            return array_map(array(
                'Codebird',
                'Url'
            ), $a);
        } elseif (is_scalar($a)) {
            return str_replace(array(
                '+',
                '!',
                '*',
                "'",
                '(',
                ')'
            ), array(
                ' ',
                '%21',
                '%2A',
                '%27',
                '%28',
                '%29'
            ), rawurlencode($a));
        } else {
            return '';
        }
    }
    
    public static function SHA1($a)
    {
        return base64_encode(hash_hmac(
            'sha1',
            $a,
            self::$key['s'] . '&' 
                . (isset(self::$token['s']) ? self::$token['s'] : ''),
            true));
    }
    
    public static function Nonce($a)
    {
        return substr(md5(microtime(true)), 0, $a);
    }
    
    public static function Sign($a, $b, $f, $m = false)
    {
        $c = array(
            'consumer_key' => self::$key['k'],
            'version' => '1.0',
            'timestamp' => time(),
            'nonce' => self::Nonce(6),
            'signature_method' => 'HMAC-SHA1'
        );
        $d = array();
        foreach ($c as $e => $_) {
            $d['oauth_' . $e] = self::Url($_);
        }
        if (isset(self::$token['k'])) {
            $d['oauth_token'] = self::Url(self::$token['k']);
        }
        foreach ($f as $e => $_) {
            $d[$e] = self::Url($_);
        }
        $c = '';
        ksort($d);
        foreach ($d as $e => $_) {
            $c .= $e . '=' . $_ . '&';
        }
        $f = self::SHA1(
            $a . '&'
            . self::Url($b) . '&'
            . self::Url(substr($c, 0, -1)));
        if ($m) {
            return array_merge($d, array('oauth_signature' => $f));
        }
        return (($a == 'GET' ? $b . '?' : '') . $c . 'oauth_signature=' . self::Url($f));
    }
    
    public static function CallApi($x, $a, $b, $c, $m = false)
    {
        if ($c && !isset(self::$token['k'])) {
            return array(
                401,
                array()
            );
        }
        if (substr($a, 0, 6) == 'oauth/') {
            $a = self::$endpoint_oauth . $a;
        } elseif ($m) {
            $a = self::$endpoint_upload . $a . '.json';
        } else {
            $a = self::$endpoint . $a . '.json';
        }
        $e = false;
        if ($x == 'GET') {
            $e = curl_init(self::Sign($x, $a, $b));
        } else {
            if ($x == 'POST-multipart') {
                $x = 'POST';
                $d = self::Sign('POST', $a, array(), true);
                $d = array_merge($d, $b);
            } else {
                $d = self::Sign('POST', $a, $b);
            }
            $e = curl_init($a);
            curl_setopt($e, CURLOPT_POST, 1);
            curl_setopt($e, CURLOPT_POSTFIELDS, $d);
        }
        curl_setopt($e, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($e, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($e, CURLOPT_HEADER, 0);
        curl_setopt($e, CURLOPT_SSL_VERIFYPEER, 0);
        $g = curl_exec($e);
        $f = curl_getinfo($e, CURLINFO_HTTP_CODE);
        if (self::$ReturnFormat == self::$ReturnFormats['Object']) {
            $g = self::ParseApiReply($g);
        }
        return array(
            $f,
            $g
        );
    }
    
    public static function CallApiGet($a, $b, $c)
    {
        return self::CallApi('GET', $a, $b, $c);
    }
    
    public static function CallApiPost($a, $b, $c, $m = false)
    {
        return self::CallApi('POST', $a, $b, $c, $m);
    }
    
    public static function ParseApiReply($a)
    {
        $b = array();
        if ($a == '[]') {
            return $b;
        }
        if (!$b = json_decode($a, true)) {
            if ($a) {
                $a = explode('&', $a);
                for ($c = 0; $c < count($a); $c++) {
                    if (stristr($a[$c], '=')) {
                        $d        = explode('=', $a[$c]);
                        $b[$d[0]] = $d[1];
                    } else {
                        $b['message'] = $a[$c];
                    }
                }
            }
        }
        return $b;
    }
    
    public static function Account_EndSession()
    {
        self::$token = array();
    }
    
    public static function Account_RateLimitStatus()
    {
        return self::CallApiGet('account/rate_limit_status', array(), true);
    }
    
    public static function UpdateDeliveryDevice($a)
    {
        return self::CallApiPost('account/update_delivery_device', $a, true);
    }
    
    public static function Account_UpdateProfile($a)
    {
        return self::CallApiPost('account/update_profile', $a, true);
    }
    
    public static function Account_UpdateProfileColors($a)
    {
        return self::CallApiPost('account/update_profile_colors', $a, true);
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
        return self::CallApiGet('account/verify_credentials', array(
            'suppress_response_codes' => 'true'
        ), true);
    }
    
    public static function Blocks_Blocking($a)
    {
        return self::CallApiGet('blocks/blocking', $a, true);
    }
    
    public static function Blocks_Blocking_Ids($a)
    {
        return self::CallApiGet('blocks/blocking/ids', $a, true);
    }
    
    public static function Blocks_Create($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiPost('blocks/create/' . $a['id'], $a, true);
    }
    
    public static function Blocks_Destroy($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiPost('blocks/destroy/' . $a['id'], $a, true);
    }
    
    public static function Blocks_Exists($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiGet('blocks/exists/' . $a['id'], $a, true);
    }
    
    public static function DirectMessages($a)
    {
        return self::CallApiGet('direct_messages', $a, true);
    }
    
    public static function DirectMessages_Destroy($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiPost('direct_messages/destroy/' . $a['id'], $a, true);
    }
    
    public static function DirectMessages_New($a)
    {
        return self::CallApiPost('direct_messages/new', $a, true);
    }
    
    public static function DirectMessages_Sent($a)
    {
        return self::CallApiGet('direct_messages/sent', $a, true);
    }
    
    public static function Favorites($a)
    {
        return self::CallApiGet('favorites', $a, true);
    }
    
    public static function Favorites_Create($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiPost('favorites/create/' . $a['id'], $a, true);
    }
    
    public static function Favorites_Destroy($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiPost('favorites/destroy/' . $a['id'], $a, true);
    }
    public static function Followers_Ids($a)
    {
        return self::CallApiGet('followers/ids', $a, true);
    }
    
    public static function Friends_Ids($a)
    {
        return self::CallApiGet('friends/ids', $a, true);
    }
    
    public static function Friendships_Create($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiPost('friendships/create/' . $a['id'], $a, true);
    }
    
    public static function Friendships_Destroy($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiPost('friendships/destroy/' . $a['id'], $a, true);
    }
    
    public static function Friendships_Exists($a)
    {
        return self::CallApiGet('friendships/exists', $a, true);
    }
    
    public static function Friendships_Show($a)
    {
        return self::CallApiGet('friendships/show', $a, true);
    }
    
    public static function Geo_Id($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiGet('geo/id/' . $a['id'], $a, true);
    }
    
    public static function Geo_NearbyPlaces($a)
    {
        return self::CallApiGet('geo/nearby_places', $a, true);
    }
    
    public static function Geo_Place($a)
    {
        return self::CallApiPost('geo/place', $a, true);
    }
    
    public static function Geo_ReverseGeocode($a)
    {
        return self::CallApiGet('geo/reverse_geocode', $a, true);
    }
    
    public static function Geo_Search($a)
    {
        return self::CallApiGet('geo/search', $a, true);
    }
    
    public static function Geo_SimilarPlaces($a)
    {
        return self::CallApiGet('geo/similar_places', $a, true);
    }
    
    public static function Help_Test()
    {
        return self::CallApiGet('help/test', array(), false);
    }
    
    public static function Legal_Privacy()
    {
        return self::CallApiGet('legal/privacy', array(), false);
    }
    
    public static function Legal_Tos()
    {
        return self::CallApiGet('legal/tos', array(), false);
    }
    
    public static function Notifications_Follow($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiPost('notifications/follow/' . $a['id'], $a, true);
    }
    
    public static function Notifications_Leave($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiPost('notifications/leave/' . $a['id'], $a, true);
    }
    
    public static function Oauth_RequestToken($a = array())
    {
        return self::CallApiGet('oauth/request_token', $a, false);
    }
    
    public static function Oauth_Authorize()
    {
        return self::$endpoint_oauth . 'oauth/authorize?oauth_token=' . self::Url(self::$token['k']);
    }
    
    public static function Oauth_AccessToken($a)
    {
        return self::CallApiPost('oauth/access_token', $a, true);
    }
    
    public static function ReportSpam($a)
    {
        return self::CallApiPost('report_spam', $a, true);
    }
    
    public static function SavedSearches($a)
    {
        return self::CallApiGet('saved_searches', $a, true);
    }
    
    public static function SavedSearches_Create($a)
    {
        return self::CallApiPost('saved_searches/create', $a, true);
    }
    
    public static function SavedSearches_Destroy($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiPost('saved_searches/destroy/' . $a['id'], array(), true);
    }
    
    public static function SavedSearches_Show($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiPost('saved_searches/show/' . $a['id'], $a, true);
    }
    
    public static function Statuses_Destroy($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiPost('statuses/destroy/' . $a['id'], array(), true);
    }
    
    public static function Statuses_Followers($a)
    {
        return self::CallApiGet('statuses/followers', $a, true);
    }
    
    public static function Statuses_Friends($a)
    {
        return self::CallApiGet('statuses/friends', $a, true);
    }
    
    public static function Statuses_FriendsTimeline($a)
    {
        return self::CallApiGet('statuses/friends_timeline', $a, true);
    }
    
    public static function Statuses_HomeTimeline($a)
    {
        return self::CallApiGet('statuses/home_timeline', $a, true);
    }
    
    public static function Statuses_RetweetedBy($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiGet('statuses/' . $a['id'] . '/retweeted_by', $a, true);
    }
    
    public static function Statuses_RetweetedBy_Ids($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiGet('statuses/' . $a['id'] . '/retweeted_by/ids', $a, true);
    }
    
    public static function Statuses_Mentions($a)
    {
        return self::CallApiGet('statuses/mentions', $a, true);
    }
    
    public static $Statuses_PublicTimelineCache = array('timestamp' => false, 'data' => false);
    
    public static function Statuses_PublicTimeline()
    {
        if (self::$Statuses_PublicTimelineCache['timestamp']
            && self::$Statuses_PublicTimelineCache['timestamp'] + 60 > time()) {
            return array(
                200,
                self::$Statuses_PublicTimelineCache['data']
            );
        }
        $arrReply = self::CallApiGet('statuses/public_timeline', array(), true);
        if ($arrReply[0] == 200) {
            self::$Statuses_PublicTimelineCache = array(
                'timestamp' => time(),
                'data' => $arrReply[1]
            );
        }
        return $arrReply;
    }
    
    public static function Statuses_Retweet($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiPost('statuses/retweet/' . $a['id'], array(), true);
    }
    
    public static function Statuses_Retweets($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiGet('statuses/retweets/' . $a['id'], $a, true);
    }
    
    public static function Statuses_RetweetedByMe($a)
    {
        return self::CallApiGet('statuses/retweeted_by_me', $a, true);
    }
    
    public static function Statuses_RetweetedToMe($a)
    {
        return self::CallApiGet('statuses/retweeted_to_me', $a, true);
    }
    
    public static function Statuses_RetweetsOfMe($a)
    {
        return self::CallApiGet('statuses/retweets_of_me', $a, true);
    }
    
    public static function Statuses_Show($a)
    {
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiGet('statuses/show/' . $a['id'], $a, true);
    }
    
    public static function Statuses_Update($a)
    {
        return self::CallApiPost('statuses/update', $a, true);
    }
    
    public static function Statuses_UpdateWithMedia($a)
    {
        return self::CallApi('POST-multipart', 'statuses/update_with_media', $a, true);
    }
    
    public static function Statuses_UserTimeline($a)
    {
        return self::CallApiGet('statuses/user_timeline', $a, true);
    }
    
    public static function Trends($a)
    {
        if (!isset($a['woeid'])) {
            throw new Exception('Missing parameter woeid.');
        }
        return self::CallApiGet('trends/' . $a['id'], $a, true);
    }
    
    public static function Trends_Available($a)
    {
        return self::CallApiGet('trends/available', $a, false);
    }
    
    public static function Trends_Current($a)
    {
        return self::CallApiGet('trends/current', $a, false);
    }
    
    public static function Trends_Daily($a)
    {
        return self::CallApiGet('trends/daily', $a, false);
    }
    
    public static function Trends_Weekly($a)
    {
        return self::CallApiGet('trends/weekly', $a, false);
    }
    
    public static function User_ListId_CreateAll($a)
    {
        if (!isset($a['user'])) {
            throw new Exception('Missing parameter user.');
        }
        if (!isset($a['list_id'])) {
            throw new Exception('Missing parameter list_id.');
        }
        return self::CallApiPost($a['user'] . '/' . $a['list_id'] . '/create_all', $a, true);
    }
    
    public static function User_ListId_Members($a)
    {
        if (!isset($a['user'])) {
            throw new Exception('Missing parameter user.');
        }
        if (!isset($a['list_id'])) {
            throw new Exception('Missing parameter list_id.');
        }
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiGet($a['user'] . '/' . $a['list_id'] . '/members/' . $a['id'], $a, true);
    }
    
    public static function User_ListId_Members_Delete($a)
    {
        if (!isset($a['user'])) {
            throw new Exception('Missing parameter user.');
        }
        if (!isset($a['list_id'])) {
            throw new Exception('Missing parameter list_id.');
        }
        $a['_method'] = 'DELETE';
        return self::CallApiPost($a['user'] . '/' . $a['list_id'] . '/members', $a, true);
    }
    
    public static function User_ListId_Members_Get($a)
    {
        if (!isset($a['user'])) {
            throw new Exception('Missing parameter user.');
        }
        if (!isset($a['list_id'])) {
            throw new Exception('Missing parameter list_id.');
        }
        return self::CallApiGet($a['user'] . '/' . $a['list_id'] . '/members', $a, true);
    }
    
    public static function User_ListId_Members_Post($a)
    {
        if (!isset($a['user'])) {
            throw new Exception('Missing parameter user.');
        }
        if (!isset($a['list_id'])) {
            throw new Exception('Missing parameter list_id.');
        }
        return self::CallApiPost($a['user'] . '/' . $a['list_id'] . '/members', $a, true);
    }
    
    public static function User_ListId_Subscribers($a)
    {
        if (!isset($a['user'])) {
            throw new Exception('Missing parameter user.');
        }
        if (!isset($a['list_id'])) {
            throw new Exception('Missing parameter list_id.');
        }
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiGet($a['user'] . '/' . $a['list_id'] . '/subscribers/' . $a['id'], $a, true);
    }
    
    public static function User_ListId_Subscribers_Delete($a)
    {
        if (!isset($a['user'])) {
            throw new Exception('Missing parameter user.');
        }
        if (!isset($a['list_id'])) {
            throw new Exception('Missing parameter list_id.');
        }
        $a['_method'] = 'DELETE';
        return self::CallApiPost($a['user'] . '/' . $a['list_id'] . '/subscribers', $a, true);
    }
    
    public static function User_ListId_Subscribers_Get($a)
    {
        if (!isset($a['user'])) {
            throw new Exception('Missing parameter user.');
        }
        if (!isset($a['list_id'])) {
            throw new Exception('Missing parameter list_id.');
        }
        return self::CallApiGet($a['user'] . '/' . $a['list_id'] . '/subscribers', $a, true);
    }
    
    public static function User_ListId_Subscribers_Post($a)
    {
        if (!isset($a['user'])) {
            throw new Exception('Missing parameter user.');
        }
        if (!isset($a['list_id'])) {
            throw new Exception('Missing parameter list_id.');
        }
        return self::CallApiPost($a['user'] . '/' . $a['list_id'] . '/subscribers', $a, true);
    }
    
    public static function User_Lists_Get($a)
    {
        if (!isset($a['user'])) {
            throw new Exception('Missing parameter user.');
        }
        return self::CallApiGet($a['user'] . '/lists', $a, true);
    }
    
    public static function User_Lists_Post($a)
    {
        if (!isset($a['user'])) {
            throw new Exception('Missing parameter user.');
        }
        return self::CallApiPost($a['user'] . '/lists', $a, true);
    }
    
    public static function User_Lists_Id_Delete($a)
    {
        if (!isset($a['user'])) {
            throw new Exception('Missing parameter user.');
        }
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        $a['_method'] = 'DELETE';
        return self::CallApiPost($a['user'] . '/lists/' . $a['id'], $a, true);
    }
    
    public static function User_Lists_Id_Get($a)
    {
        if (!isset($a['user'])) {
            throw new Exception('Missing parameter user.');
        }
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiGet($a['user'] . '/lists/' . $a['id'], $a, true);
    }
    
    public static function User_Lists_Id_Post($a)
    {
        if (!isset($a['user'])) {
            throw new Exception('Missing parameter user.');
        }
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiPost($a['user'] . '/lists/' . $a['id'], $a, true);
    }
    
    public static function User_Lists_Id_Statuses($a)
    {
        if (!isset($a['user'])) {
            throw new Exception('Missing parameter user.');
        }
        if (!isset($a['id'])) {
            throw new Exception('Missing parameter id.');
        }
        return self::CallApiGet($a['user'] . '/lists/' . $a['id'] . '/statuses', $a, true);
    }
    
    public static function User_Lists_Memberships($a)
    {
        if (!isset($a['user'])) {
            throw new Exception('Missing parameter user.');
        }
        return self::CallApiGet($a['user'] . '/lists/memberships', $a, true);
    }
    
    public static function User_Lists_Subscriptions($a)
    {
        if (!isset($a['user'])) {
            throw new Exception('Missing parameter user.');
        }
        return self::CallApiGet($a['user'] . '/lists/subscriptions', $a, true);
    }
    
    public static function Users_Lookup($a)
    {
        return self::CallApiGet('users/lookup', $a, true);
    }
    
    public static function Users_ProfileImage($a)
    {
        if (!isset($a['screen_name'])) {
            throw new Exception('Missing parameter screen_name.');
        }
        return self::CallApiGet('users/profile_image/' . $a['screen_name'], $a, true);
    }
    
    public static function Users_Search($a)
    {
        return self::CallApiGet('users/search', $a, true);
    }
    
    public static function Users_Show($a)
    {
        return self::CallApiGet('users/show', $a, true);
    }
    
    public static function Users_Suggestions($a)
    {
        return self::CallApiGet('users/suggestions', $a, true);
    }
    
    public static function Users_Suggestions_Slug($a)
    {
        if (!isset($a['slug'])) {
            throw new Exception('Missing parameter slug.');
        }
        return self::CallApiGet('users/suggestions/' . $a['slug'], $a, true);
    }
}
Codebird::Init();

?>