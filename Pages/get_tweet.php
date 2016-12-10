<?php 
//require_once('TwitterAPIExchange.php');
require_once __DIR__ . '/../vendor/autoload.php';
/** Set access tokens here - see: https://dev.twitter.com/apps/ **/

$settings = array(
    'oauth_access_token' => "558263780-j6HbHcOcbcfgmejhTOLRzktRKlIsFGAQhtBbDCiD",
    'oauth_access_token_secret' => "ySQcoLElsg87quG9YwK6ffIOlbbNuahAjXg5cifmjcuxu",
    'consumer_key' => "QuzHMuZYkF4cpRJHW5KkCf8VB",
    'consumer_secret' => "KpMSzgDzv0Jl0sHjUMtXDe3nH30Gz5LOHUs7GytlxTdVBxWxcj"
);
$url = 'https://api.twitter.com/1.1/blocks/create.json';
$requestMethod = 'POST';
$postfields = array(
    'screen_name' => 'idakhamidah', 
    'skip_status' => '1'
);
$twitter = new TwitterAPIExchange($settings);
echo $twitter->buildOauth($url, $requestMethod)
    ->setPostfields($postfields)
    ->performRequest();
$url = 'https://api.twitter.com/1.1/followers/ids.json';
$getfield = '?screen_name=idakhamidah';
$requestMethod = 'GET';

$twitter = new TwitterAPIExchange($settings);
echo $twitter->setGetfield($getfield)
    ->buildOauth($url, $requestMethod)
    ->performRequest();
exit;


use Abraham\TwitterOAuth\TwitterOAuth;
$connection = new TwitterOAuth("QuzHMuZYkF4cpRJHW5KkCf8VB", "KpMSzgDzv0Jl0sHjUMtXDe3nH30Gz5LOHUs7GytlxTdVBxWxcj", "558263780-AxSfxXLgqxEm8FSeN8ksTapXc8Q65a7Svh8cFNm3", "ySQcoLElsg87quG9YwK6ffIOlbbNuahAjXg5cifmjcuxu");

$content = $connection->get("account/verify_credentials");
print_r($content);
echo "aaa";
$statuses = $connection->get("statuses/home_timeline", array("count" => 25, "exclude_replies" => true));
print_r($statuses);
exit;

$settings = array(
    'oauth_access_token' => "558263780-AxSfxXLgqxEm8FSeN8ksTapXc8Q65a7Svh8cFNm3",
    'oauth_access_token_secret' => "ySQcoLElsg87quG9YwK6ffIOlbbNuahAjXg5cifmjcuxu",
    'consumer_key' => " QuzHMuZYkF4cpRJHW5KkCf8VB",
    'consumer_secret' => "KpMSzgDzv0Jl0sHjUMtXDe3nH30Gz5LOHUs7GytlxTdVBxWxcj"
);
/** URL for REST request, see: https://dev.twitter.com/docs/api/1.1/ **/
$url = 'https://api.twitter.com/1.1/blocks/create.json';
$requestMethod = 'POST';
/** POST fields required by the URL above. See relevant docs as above **/
$postfields = array(
    'screen_name' => 'idakhamidah', 
    'skip_status' => '1'
);
/** Perform a POST request and echo the response **/
$twitter = new TwitterAPIExchange($settings);
echo $twitter->buildOauth($url, $requestMethod)
             ->setPostfields($postfields)
             ->performRequest();
/** Perform a GET request and echo the response **/
/** Note: Set the GET field BEFORE calling buildOauth(); **/
$url = 'https://api.twitter.com/1.1/followers/ids.json';
$getfield = '?screen_name=J7mbo';
$requestMethod = 'GET';
$twitter = new TwitterAPIExchange($settings);
echo $twitter->setGetfield($getfield)
             ->buildOauth($url, $requestMethod)
             ->performRequest();
?>