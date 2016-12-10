<?php 
require_once('Pages/TwitterAPIExchange.php');

// include composer autoloader
require_once __DIR__ . '/../vendor/autoload.php'; 
  
// create stemmer
// cukup dijalankan sekali saja, biasanya didaftarkan di service container
$stemmerFactory = new \Sastrawi\Stemmer\StemmerFactory();
$stemmer  = $stemmerFactory->createStemmer();
 
##ambil data tweet
 $settings = array(
      'oauth_access_token' => "558263780-j6HbHcOcbcfgmejhTOLRzktRKlIsFGAQhtBbDCiD",
      'oauth_access_token_secret' => "o8a83KLXNb7EqLnVwNs27Fn9guASl2JcKLMFcqxysOBbv",
      'consumer_key' => "QuzHMuZYkF4cpRJHW5KkCf8VB",
      'consumer_secret' => "KpMSzgDzv0Jl0sHjUMtXDe3nH30Gz5LOHUs7GytlxTdVBxWxcj"
  );
 
  $url = 'https://api.twitter.com/1.1/search/tweets.json';
  $requestMethod = 'GET';
  $getfield = '?q=%40express_group&result_type=mixed&count=1000';

  $twitter = new TwitterAPIExchange($settings);
  $text_tweet_mention = $twitter->setGetfield($getfield)
               ->buildOauth($url, $requestMethod)
               ->performRequest();

  $text_tweet_mention_json_decode = json_decode($text_tweet_mention);
  /*
  echo "<pre>";
  print_r($text_tweet_mention_json_decode);
  exit;
*/

##end ambil data tw :p

  
  mysql_query("TRUNCATE tweets"); 

  foreach($text_tweet_mention_json_decode->statuses as $tweet_mention){
    $date=date_create($tweet_mention->created_at);
    $tweet_date = date_format($date,"Y/m/d H:i:s");

    mysql_query("insert into tweets (tweet_id,tweet,username,tweet_date) 
        values ('".$tweet_mention->id_str."','".$tweet_mention->text."',
            '".$tweet_mention->user->screen_name."','$tweet_date') ");
  }

?>


<h3><i class="fa fa-angle-right"></i> Preprocessing</h3>
<div class="row">
 <div class="col-sm-12">
  	  <div class="showback">
  	  	  <h4><i class="fa fa-angle-right"></i> Data Preprocessing</h4>
            <form action="" method='post'>
      	  	  <table id="tabel1" class="ui celled table" cellspacing="0" width="100%" >
                  <thead>
                  <tr>
                      <th>No</th>
                      <th>Tweet date</th>
                      <th>Tweet</th>
                      <?php
                      if(isset($_POST['btnPre'])){
                        echo "<th>Tweet bersih</th>";
                      }
                      ?>
                      
                  </tr>
                  </thead>
                  <tbody>
                	   <?php 
                      mysql_query("TRUNCATE `tweet_bersih`");
                      $sql = mysql_query("select tweet_date,tweet,username from tweets");
                      $no=1;
                      $tweets = array();
                      
                      while($d = mysql_fetch_array($sql)){
                          $tweets[] = $d;
                          $pre="";
                          if(isset($_POST['btnPre'])){
                            $pre = pre_processing($d,$stemmer);
                            echo "<tr>
                                  <td>$no</td>
                                  <td>$d[tweet_date]</td>
                                  <td>$d[tweet]</td>
                                  <td>".$pre."</td>
                                </tr>";
                          }else{
                            echo "<tr>
                                  <td>$no</td>
                                  <td>$d[tweet_date]</td>
                                  <td>$d[tweet]</td> 
                                </tr>";
                          }
                        $no++;
                      }

                    ?>
                  </tbody>
              </table>
              <br>
                <button type="submit" name="btnPre" class="btn btn-theme">Pre</button>
            </form>  
  	  </div><!--/showback-->
  </div><!-- /col-md-12 -->
</div><!-- /row -->

<?php
  function pre_processing($tweet=array(),$stemmer){
      $text_Concat = array();
      if(substr($tweet['tweet'], 0, 3) != 'RT '){
        $text = trim($tweet['tweet']);

        //1. Buang link
        $regex = "@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@";
        $text = preg_replace($regex, '', $text);

        //2. Buang @blablabla
        $regex = '/(\s+|^)@\S+/';
        $text = preg_replace($regex, '', $text);

        //3. Buang tanda baca (. , ! " ? #)
        $text = omitCharacters($text, null);

        //5. To Lower
        $text = strtolower($text);

        //6. Stemming 
        $text = $stemmer->stem($text);
        
        // Replacement kata baku
        $text = explode(" ", $text); //token
        $text = array_filter($text); //hapus array yg bernilai kosong
        $text = wordReplacement($text);   
        
        // Stop Word Removal
        $text = implode(" ", $text); //kalimaat
        $text = stopWordRemoval($text);
        $text = trim($text);
         
        // Tokenize
        $text = explode(" ", $text);
        //$result = array_filter( array( "0",0,1,2,3,'text' ) , 'is_numeric' );
        $text = array_filter($text,'is_string');
        $text = array_filter($text); //hapus array yg bernilai kosong

        //$text = array_filter($text, 'is_string') + array_values($text);
        $text = filter_angka($text);//hapus kata yg mengandung lemak/angka
        //hapus kata yg jml hurufnya 1 atau 2
        $text = hapus_huruf($text);
        
        //penggabungan tidak
        $text = clusterTidak($text);  
        
        $text = implode(" ",$text);

        if($text!="") mysql_query("insert into tweet_bersih values ('','$text','')");

        return $text;

        //
      }
          //if($text!="") $Tweets[] = array('text'=>$text,'text_all'=>$tweet['tweet'] ); //, 'label'=>$tweet->label);
        //}
  }

  function filter_angka($text=array()){
     $ar_baru=array(); 
     foreach ($text as $key => $kata){
        if(!empty($kata)){
           $eh_ketemu_si_gedut = false;
            for($i=0;$i<=strlen($kata)-1;$i++){
              if(is_numeric($kata[$i]) || empty($kata[$i])) $eh_ketemu_si_gedut = true;
            }
            if($eh_ketemu_si_gedut==false) $ar_baru[] = $kata;
          }
     }
     return $ar_baru;
  }

  //hapus huruf 1 atau 2
  function hapus_huruf($text=array()){
     $ar_baru=array(); 
     foreach ($text as $key => $kata){
         if(trim($kata)!="")
         if(strlen(trim($kata))>2) $ar_baru[] = $kata;
     }
     return $ar_baru;
  }


  function clusterTidak($text=array()){
     $baskom=array();
     for($i=0;$i<=count($text)-1;$i++){
        if($text[$i]=="tidak"){
          if(isset($text[$i]) && isset($text[$i+1]))
            $baskom[]=$text[$i]."".$text[$i+1];
          else $baskom[] = $text[$i];
          $i++;
        }else $baskom[] = $text[$i]; 
     }
     return $baskom;
  }

  function omitCharacters($text=array(), $replaceArray=array()) {
    $replacelist = array(
      '.',',',"\'",':',';','=','*','%',"\"",'?','!','#','-','_','+','=','$',"\/","\\",'(',')'
    );
    if ( $replaceArray ) {
      $replacelist = $replaceArray;
    }
    foreach($replacelist as $list){
      $text = str_replace($list, ' ', $text);
    }
    return $text;
  }

  function stopWordRemoval($text=array()){
    $sql = mysql_query("select id,stopword from stopwords");
    $stopwords = array();
    $kata = "";
    while($stopword=mysql_fetch_array($sql)){
      $kata .= $stopword['stopword']."|";
    }
    $regex = "/\b(".$kata.")\b/";
    return preg_replace($regex, '', $text);
  }

  function wordReplacement($text=array()){
    foreach ($text as $k=>$v) {
      $sql = mysql_query("select kata,vocab_id,katadasar from replace_words
         join vocabs on vocabs.id = vocab_id where kata='$v'");
      $d = mysql_fetch_array($sql); 
      if(mysql_num_rows($sql)>0) $text[$k] = $d['katadasar'];
    }
    return $text;
  }

?>
