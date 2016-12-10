<?php
require_once('Pages/TwitterAPIExchange.php'); 
  ##ambil data tweet
  $settings = array(
      'oauth_access_token' => "558263780-j6HbHcOcbcfgmejhTOLRzktRKlIsFGAQhtBbDCiD",
      'oauth_access_token_secret' => "o8a83KLXNb7EqLnVwNs27Fn9guASl2JcKLMFcqxysOBbv",
      'consumer_key' => "QuzHMuZYkF4cpRJHW5KkCf8VB",
      'consumer_secret' => "KpMSzgDzv0Jl0sHjUMtXDe3nH30Gz5LOHUs7GytlxTdVBxWxcj"
  );
 
  /*$url = 'https://api.twitter.com/1.1/friendships/create.json';
  $requestMethod = 'POST';
  $screen_name = 'wira_ar_rifai'; 
  $getfield = 'screen_name=wira_ar_rifai;follow=true';

  $twitter = new TwitterAPIExchange($settings);
  $text_tweet_mention = $twitter->setGetfield($getfield)
               ->buildOauth($url, $requestMethod)
               ->performRequest();
*/

  $url = 'https://api.twitter.com/1.1/friendships/create.json';
  $postfields = array('screen_name' => 'RHMediaCreative');
  $requestMethod = 'POST';

  $TwitterAPIExchange = new TwitterAPIExchange($settings);
  $response = $TwitterAPIExchange->setPostfields ($postfields)
          ->buildOauth($url, $requestMethod)
          ->performRequest();

  $text_response = json_decode($response);
  echo "<pre>";
  print_r($text_response);
?>
 