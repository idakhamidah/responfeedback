<?php 
  require_once('Pages/TwitterAPIExchange.php');
  include "Lib/function_pre_processing.php";
  require_once "Lib/kint/Kint.class.php";

  #ambil data tweet
   // $settings = array(
   //      'oauth_access_token' => "558263780-j6HbHcOcbcfgmejhTOLRzktRKlIsFGAQhtBbDCiD",
   //      'oauth_access_token_secret' => "o8a83KLXNb7EqLnVwNs27Fn9guASl2JcKLMFcqxysOBbv",
   //      'consumer_key' => "QuzHMuZYkF4cpRJHW5KkCf8VB",
   //      'consumer_secret' => "KpMSzgDzv0Jl0sHjUMtXDe3nH30Gz5LOHUs7GytlxTdVBxWxcj"
   // );
   $settings = array(
        'oauth_access_token' => "790400892226772992-om8f2iIaaK2s1dQOzmR48tJnZP7mXKT",
        'oauth_access_token_secret' => "5HKmWLc46s4GtSHRCM2lKektsmLrMnVQ0ZK4LQcQ4eknz",
        'consumer_key' => "37r2vH4pNZJzOW16NONsw3p9a",
        'consumer_secret' => "CmAFWMmXj6rhBph94DTneaSafNGkwiYegu6ZH6JJmjxEj8vB1a"
    );
   
    $url = 'https://api.twitter.com/1.1/search/tweets.json';
    $requestMethod = 'GET';
    $getfield = '?q=%40Express_Group&result_type=mixed&count=1000';

    $twitter = new TwitterAPIExchange($settings);
    $text_tweet_mention = $twitter->setGetfield($getfield)
                 ->buildOauth($url, $requestMethod)
                 ->performRequest();

    $text_tweet_mention_json_decode = json_decode($text_tweet_mention);
/*
    echo "<pre>";
    print_r($text_tweet_mention_json_decode->statuses[0]);
  */  //exit;
 
    $tweet_baru = $text_tweet_mention_json_decode->statuses[0];
    $text_tweet = $tweet_baru->text;
    $id_tweet = $tweet_baru->id_str;
    $screen_name =  $tweet_baru->user->screen_name;
    //echo $text_tweet;
    //exit;


    //cek ada tweet gak
    if(isset($id_tweet)){
      $sql = mysql_query("select tweet_id from tweets where tweet_id='$id_tweet' and id_tweet_respon='0' "); 
      $cek_tweet = mysql_num_rows($sql);

      //jika tidak ada tweet baru maka akan muncul alert info
      if($cek_tweet<1) {
        echo "<div class='alert alert-info' style='margin-top: 35px;'>Belum ada tweet baru</div>";
      }

      //jika ada tweet baru
      if($cek_tweet>0){
         $tweet_bersih=pre_processing(array('tweet'=>$text_tweet)); //preprocessing tweet

         //pengambilan repons tweet berdsarkan kelas identifikasi // kelas 1,2,3,4
         if($tweet_bersih!=""){ //jika bukan RT
            //print_r($tweet_bersih);

            ######1. nbc######
            $tweets_baru = $tweet_bersih;
            $no=0; 
            //kata unik
            $sql_kataunik = mysql_query("select * from nbc_kataunik");
            $kataunik = array();
            while($d_unik = mysql_fetch_array($sql_kataunik)){
              $kataunik[] = $d_unik;
            }
             
            $data_testing[] = array( "text" => explode(" ", $tweet_bersih) );
   
              //load kelas prior
             $sql_prior = mysql_query("select * from nbc_priorclass");
             $prior = array();
             while($dprior = mysql_fetch_array($sql_prior)){
                $prior[$dprior['class']] = $dprior['prior'];
             }
             
            // ddd($prior[2]);        
            foreach($data_testing as $i=>$tt) {
                // $score[$i]=array(1=>$prior[2]['prior'],
                //                  2=>$prior[0]['prior'],
                //                  3=>$prior[1]['prior'],
                //                  4=>$prior[3]['prior']);
                $score[$i]=array(1=>$prior[1], // prior pujian
                                 2=>$prior[2], // prior keluhan
                                 3=>$prior[3], // prior follow
                                 4=>$prior[4]); // prior unknown
                
                $score[$i]['text'] = $tt['text'];
                foreach($tt['text'] as $t){
                    foreach ($kataunik as $key=>$d) {
                      if(isset($d['kata'])){
                        if($d['kata']==$t){
                           $score[$i][1] = $score[$i][1] * $d['pPujian'];
                           $score[$i][2] = $score[$i][2] * $d['pKeluhan'];
                           $score[$i][3] = $score[$i][3] * $d['pFollow'];
                           $score[$i][4] = $score[$i][4] * $d['pUnknown'];
                        }
                      }
                    }
                }

                // //echo "<br> Probabilitas Posterior kelas Pujian = ";
                // print (number_format($score[$i][1],14,",","."));
                // echo "<br> Probabilitas Posterior kelas Keluhan = ";
                // print (number_format($score[$i][2],14,",","."));
                // echo "<br> Probabilitas Posterior kelas Follow = ";
                // print (number_format($score[$i][3],14,",","."));
                // echo "<br> Probabilitas Posterior kelas Unknown = ";
                // print (number_format($score[$i][4],14,",","."));
                // echo "<br>";
            }
          
            $benar = 0;
            foreach($data_testing as $i=>$tt) { 
               $max = $score[$i][1];
               $kelas = 1;
               if($score[$i][2] > $max){
                 $max=$score[$i][2];
                 $kelas = 2;
               }
               if($score[$i][3] > $max) {
                 $max=$score[$i][3];
                 $kelas = 3;
               }
               if($score[$i][4] > $max) {
                 $max=$score[$i][4];
                 $kelas = 4;
               }  
            }
            //1 pujian, 2 keluhan, 3 follow, 4 un
            $id_tweet_respon=1;
            if($kelas==1){
               $id_tweet_respon=1; //pujian
            }elseif($kelas==3){
               $id_tweet_respon=2;//follow
            } 

            if($kelas==2){
                ######2. roc######
                $kata_d['kata'] = explode(' ',strtolower($tweets_baru));
                //$kata_d['kelas'] = $label;
                //$kata_d['id_tweet_nbc'] = 1;//$id_tweet_nbc; 
                $cek = klasifikasi_rocchio($kata_d);
                if($cek==1){ //umum
                   $id_tweet_respon=4;
                }else{
                   $id_tweet_respon=3;
                }
                ######2. roc###### 
            }
            if($kelas!=4){  //selain kelas unknown tweet akan direspons
               $s = mysql_query("select * from tweet_response where id='$id_tweet_respon'");
               $d = mysql_fetch_array($s);
                
                //follow akun 
                if($id_tweet_respon==2){
                  $url = 'https://api.twitter.com/1.1/friendships/create.json';
                  $postfields = array('screen_name' => $screen_name);
                  $requestMethod = 'POST';
                  $TwitterAPIExchange = new TwitterAPIExchange($settings);
                  $response = $TwitterAPIExchange->setPostfields ($postfields)
                          ->buildOauth($url, $requestMethod)
                          ->performRequest();
                }           
                
                echo "<div class='alert alert-success' style='margin-top: 17px'>Tweet \"".$text_tweet."\" telah direspon</div>";
                //kirim balasan
                $url = 'https://api.twitter.com/1.1/statuses/update.json';
                $requestMethod = 'POST';
                $postfields = array(
                  "status" => "@".$screen_name." ".$d['tweetresponse'],//." ".date('YmdHis'), //tulis pesan yg mau dipost
                  "trim_user"=>true, //cari di dev.twitter.com/rest/reference/post/statuses/update
                  "in_reply_to_status_id"=> "%40".$screen_name 
                ); 
                $twitter = new TwitterAPIExchange($settings);
                $text_tweet_mention = $twitter->buildOauth($url, $requestMethod)
                           ->setPostfields($postfields)
                           ->performRequest();
                $text_tweet_mention_json_decode = json_decode($text_tweet_mention);
                  
                // echo "Tweet sudah di respon";
                // echo "<pre>";
                // print_r($text_tweet_mention_json_decode);

                mysql_query("update tweets set label='$kelas',id_tweet_respon='$id_tweet_respon' 
               where tweet_id='$id_tweet' ");  

            } else {
              // klo kelas 4 (unknown)
              mysql_query("update tweets set label='$kelas' 
                where tweet_id='$id_tweet' ");              
            }

         }else{
            //tweet bersih kosong alias RT
            echo "<div class='alert alert-info' style='margin-top: 35px;'>Tweet merupakan RETWEET (RT) maka tidak direspons</div>";
         }
       

      }else{

          $sql = mysql_query("select tweet_id from tweets where tweet_id='$id_tweet' ");
          $cek_tweet = mysql_num_rows($sql);

          if($cek_tweet<1){ 
            //nambah tweet 
            $date=date_create($tweet_baru->created_at);
            $tweet_date = date_format($date,"Y/m/d H:i:s");
            mysql_query("insert into tweets (tweet_id,tweet,username,tweet_date) 
                values ('".$tweet_baru->id_str."','".$tweet_baru->text."',
                    '".$tweet_baru->user->screen_name."','$tweet_date') ");
            $tweet_bersih=pre_processing($text_tweet);
          }
 

      }
    }

  

     function klasifikasi_rocchio($kata_d=array()){ 

       $sqllatih = mysql_query("select 
                        tb.id id_tweet_bersih, tb.tweet, tr.label,
                        tn.id id_tweet_nbc
                    from tweet_bersih tb
                    left join tweet_nbc tn on tb.id=tn.id_tweet_bersih
                    join tweet_rocchio tr on tn.id=tr.id_tweet_nbc
                    where tb.status_data='training' and tn.label='2'");
                    
      $N = mysql_num_rows($sqllatih);

      $semua_kata = array();
      foreach ($kata_d['kata'] as $v1) {
          if(!array_key_exists($v1, $semua_kata)) $semua_kata[$v1] = array();
      }
      //semua kata
      $TF = array();
      // ddd($semua_kata);
      // yang lama
      // foreach($semua_kata as $key=>$val){
      //     //load semua dokumen
      //     $i=0;
      //     foreach ($kata_d['kata'] as $k) {
      //       $TF[$key][$i]=0; 
      //       if($key==$k){
      //          $TF[$key][$i] ++; 
      //       } 
      //       $i++;    
      //     }
      // } 
      // yang baru
      foreach($semua_kata as $key=>$val){
         //load semua dokumen
        $i=$N+1; 
        $TF[$key]['kelas_uji'][$i]=0;
        foreach($kata_d['kata'] as $k){
          if($key==$k){
              $TF[$key]['kelas_uji'][$i] ++; 
          }
        }  
      }

      
      foreach($TF as $index1=>$tf){
         $df=0;
          foreach($tf as $ft2){
             if($ft2 >0 ) $df++;
          }
         $IDF[$index1] = log10($N/$df) ; 
      }
      $TFIDF = array();
      $Panjang_Vektor = array();
      
      // yang asli
      // foreach($semua_kata as $key=>$val){
      //     $i=0;
      //     foreach ($kata_d as $value1) {
      //        $TFIDF[$key][$i]=$TF[$key][$i] * $IDF[$key];
      //        if(isset($Panjang_Vektor[$i])){
      //           $Panjang_Vektor[$i] += ($TFIDF[$key][$i]*$TFIDF[$key][$i]);
      //        }else{
      //           $Panjang_Vektor[$i] = ($TFIDF[$key][$i]*$TFIDF[$key][$i]);
      //        }
      //        $i++;    
      //     }
      // }

      // yang baru
      foreach($semua_kata as $key=>$val){
         $i=$N + 1; 
         $TFIDF[$key]['kelas_uji'][$i]=$TF[$key]['kelas_uji'][$i] * $IDF[$key];
         if(isset($Panjang_Vektor[$i])){
            $Panjang_Vektor[$i] += ($TFIDF[$key]['kelas_uji'][$i]*$TFIDF[$key]['kelas_uji'][$i]);
         }else{
            $Panjang_Vektor[$i] = ($TFIDF[$key]['kelas_uji'][$i]*$TFIDF[$key]['kelas_uji'][$i]);
         } 
      }

      foreach ($Panjang_Vektor as $key1=>$pv) {
         $Panjang_Vektor[$key1] = sqrt($pv);
      }

      $Normalisasi_TFIDF = array();
      // yang lama
      // foreach($semua_kata as $key=>$val){
      //     $i=0;
      //     foreach ($kata_d as $value1) {
      //        $Normalisasi_TFIDF[$key][$i] 
      //         = number_format($TFIDF[$key][$i] / $Panjang_Vektor[$i], 2) ;
      //        $i++;    
      //     }
      // }
      // yang baru
      foreach($semua_kata as $key=>$val){
         $i=$N+1;  
         $Normalisasi_TFIDF[$key]['kelas_uji'][$i] 
          = $TFIDF[$key]['kelas_uji'][$i] / $Panjang_Vektor[$i]; 
      }
      ddd($Normalisasi_TFIDF);
      
      // $sql_centroid = mysql_query("select * from rocchio_centroid");
      // $CUuji = array(1=>0,0);
      // while($c=mysql_fetch_array($sql_centroid)){
      //   //ngecek klo kata uji sudah ada di data training
      //   if(array_key_exists($c['kata'], $Normalisasi_TFIDF )){
      //     $CUuji[1] += pow($c['cUmum'] - $Normalisasi_TFIDF[$c['kata']][0] , 2);
      //     $CUuji[2] += pow($c['cSopir'] - $Normalisasi_TFIDF[$c['kata']][0], 2);
      //   }
      // }

      $sql_centroid = mysql_query("select * from rocchio_centroid");
      $CUuji = array(1=>0,0);
      while($c=mysql_fetch_array($sql_centroid)){
        //ngecek klo kata uji sudah ada di data training
        if(array_key_exists($c['kata'], $Normalisasi_TFIDF )){
          $CUuji[1] += pow($c['cUmum'] - $Normalisasi_TFIDF[$c['kata']]['kelas_uji'][$i]  , 2);
          $CUuji[2] += pow($c['cSopir'] - $Normalisasi_TFIDF[$c['kata']]['kelas_uji'][$i] , 2);
        }
      }
      //kata yg tidak ada di data training
      // $kata_normal_uji = array();
      // foreach($Normalisasi_TFIDF as $kata=>$ntf){
      //     $ada = false;
      //     while($c1=mysql_fetch_array($sql_centroid)){
      //        if($c1['kata']==$kata){
      //           $ada=true;
      //        }
      //     }
      //     if($ada==false){
      //       $CUuji[1] += pow(0 - $Normalisasi_TFIDF[$kata][0] , 2);
      //       $CUuji[2] += pow(0 - $Normalisasi_TFIDF[$kata][0], 2);
      //       $kata_normal_uji[$kata] = $ntf;
      //     }
      // }
      // ddd($CUuji);
      if($CUuji[1] < $CUuji[2]){
        return 1;
      }
      return 2;

    }
?>


<script>
    // StartRefresh();
    // function StartRefresh(){
    //   window.setTimeout(StartRefresh,360000);
    //     location.reload(true);
    // }
    window.setTimeout(function(){
      location.reload(true);
    }, 60000);
</script>
