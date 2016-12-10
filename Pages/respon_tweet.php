<?php 
  require_once('Pages/TwitterAPIExchange.php');

  $settings = array(
      'oauth_access_token' => "558263780-j6HbHcOcbcfgmejhTOLRzktRKlIsFGAQhtBbDCiD",
      'oauth_access_token_secret' => "o8a83KLXNb7EqLnVwNs27Fn9guASl2JcKLMFcqxysOBbv",
      'consumer_key' => "QuzHMuZYkF4cpRJHW5KkCf8VB",
      'consumer_secret' => "KpMSzgDzv0Jl0sHjUMtXDe3nH30Gz5LOHUs7GytlxTdVBxWxcj"
  );
  
  $url = 'https://api.twitter.com/1.1/statuses/update.json';
  $requestMethod = 'POST';
  //?status=marhaban ya ramadhan #tweet&trim_user=true&in_reply_to_status_id=@idakhamidah
  $postfields = array(
    "status" => "marhaban ya ramadhan tweet", //tulis pesan yg mau dipost
    "trim_user"=>true,
    "in_reply_to_status_id"=> "%40idakhamidah"//"@wira_ar_rifai"
  );

  $twitter = new TwitterAPIExchange($settings);
  $text_tweet_mention = $twitter->buildOauth($url, $requestMethod)
             ->setPostfields($postfields)
             ->performRequest();

  $text_tweet_mention_json_decode = json_decode($text_tweet_mention);
  echo "<pre>";
  print_r($text_tweet_mention_json_decode);


?>
