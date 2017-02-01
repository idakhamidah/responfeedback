<?php
require_once('Pages/TwitterAPIExchange.php'); 

##ambil data tweet
  $settings = array(
              'oauth_access_token' => "790400892226772992-om8f2iIaaK2s1dQOzmR48tJnZP7mXKT",
              'oauth_access_token_secret' => "5HKmWLc46s4GtSHRCM2lKektsmLrMnVQ0ZK4LQcQ4eknz",
              'consumer_key' => "37r2vH4pNZJzOW16NONsw3p9a",
              'consumer_secret' => "CmAFWMmXj6rhBph94DTneaSafNGkwiYegu6ZH6JJmjxEj8vB1a"
              );

  $url = 'https://api.twitter.com/1.1/search/tweets.json';
  $requestMethod = 'GET';
  $getfield = '?q=%40express_group&result_type=mixed&count=1000&lang=id';
  $twitter = new TwitterAPIExchange($settings);
  $text_tweet_mention = $twitter->setGetfield($getfield)
                        ->buildOauth($url, $requestMethod)
                        ->performRequest();
  $text_tweet_mention_json_decode = json_decode($text_tweet_mention);

  $tweet_baru = 0;
  foreach($text_tweet_mention_json_decode->statuses as $tweet_mention){
    $date=date_create($tweet_mention->created_at);
    $tweet_date = date_format($date,"Y/m/d H:i:s");
    $sql_cari = mysql_query("select tweet_id from tweetdownload where tweet_id='".$tweet_mention->id_str."' ");
    $count_cari = mysql_num_rows($sql_cari);
    if($count_cari < 1){
      $tweet_baru += 1;
      mysql_query("insert into tweetdownload (tweet_id,tweet,username,tweet_date) 
                  values ('".$tweet_mention->id_str."','".$tweet_mention->text."',
                          '".$tweet_mention->user->screen_name."','$tweet_date') ");
    }
  } 

?>

<?php if($tweet_baru < 1): ?>
  <div class="alert alert-info" style='margin-top: 35px;'>Belum ada tweet baru.</div>
<?php endif; ?>

<!--<h3><i class="fa fa-angle-right"></i> Feedback Tweet</h3>-->
<div class="row">
  <div class="col-sm-12\"><!--<div class="col-md-12 mt">-->
    <!--<div class="content-panel">-->
    <div class="showback">
      <table table id="tabel1_search" class="ui celled table" cellspacing="0" width="100%" border="0">
      <h4><i class="fa fa-angle-right"></i> Download Feedback Tweet</h4>
        <thead>
        <tr>
          <th width="20px">No</th>
          <th width="120">Tweet Date</th>
          <th>  Tweet</th>
        </tr>
        </thead>
        <tbody>
          <?php 
            $sql = mysql_query("select * from tweetdownload order by id desc");
            $no=1; 
            while($d = mysql_fetch_array($sql)){
              echo "<tr>
                      <td>$no</td>
                      <td>$d[tweet_date]</td>
                      <td>$d[tweet]</td> 
                    </tr>"; 
              $no++;
            } 
          ?> 
        </tbody>
      </table><br><br>
    </div> 
  </div> 
</div>

  <!--// $settings = array(
  //     'oauth_access_token' => "558263780-j6HbHcOcbcfgmejhTOLRzktRKlIsFGAQhtBbDCiD",
  //     'oauth_access_token_secret' => "o8a83KLXNb7EqLnVwNs27Fn9guASl2JcKLMFcqxysOBbv",
  //     'consumer_key' => "QuzHMuZYkF4cpRJHW5KkCf8VB",
  //     'consumer_secret' => "KpMSzgDzv0Jl0sHjUMtXDe3nH30Gz5LOHUs7GytlxTdVBxWxcj"
  // ); //inisialisasi parameter $setting-->
 