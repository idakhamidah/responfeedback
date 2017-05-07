<?php 
require "Lib/kint/Kint.class.php"; 
include "Lib/function_pre_processing.php";
?>

<!--<h3><i class="fa fa-angle-right"></i> Testing NBC</h3>-->
<div class="row">
 <div class="col-sm-12">
    <div class="showback">
      <h4><i class="fa fa-angle-right"></i> Testing Respons</h4>
      <form action="" method="post">
        <table id="tabel1" class="display" cellspacing="0" width="100%" >
            <thead>
            <tr>
                <th>No</th>
                <th>Tweet</th>
                <th>Clean Tweet</th>
                <th>Class</th>
                <th>Class (System)</th>
            </tr>
            </thead>
            <tbody>
              <?php
              $sql = mysql_query("select t.tweet tweet_kotor, tb.id id_tweet_kotor, tb.id id_tweet_bersih, tb.tweet, tn.label, tn.label_system
                                  from tweets t left join clean_tweet tb on t.id=tb.id
                                  left join tweet_nbc tn on tb.id=tn.id
                                  where tb.data_status='testing'"
                                );
              $no=1;
              $tweets = array();
              $kelas = array("1"=>"Pujian", "2"=>"Keluhan", "3"=>"Follow", "4"=>"Unknown");
              while($d = mysql_fetch_array($sql)){
                echo "<tr>
                        <td>$no</td>
                        <td>$d[tweet_kotor]
                          <input type='hidden' name='tweet_kotor[]' value='$d[tweet_kotor]' />
                          <input type='hidden' name='id_tweet_kotor[]' value='$d[id_tweet_kotor]' />
                        </td>
                        <td>$d[tweet]
                          <input type='hidden' name='id_tweet_bersih[]' value='$d[id_tweet_bersih]' />
                          <input type='hidden' name='tweet[]' value='$d[tweet]' />
                        </td>
                        <td>
                          <select name=\"label[]\">";
                            $dr_db = true;
                            foreach ($kelas as $i=>$k){
                              if($i==$d['label']){
                                $dr_db = false;
                                echo "<option selected value='$i'>$kelas[$i]</option>";
                              }else{
                                if($i==2 && $dr_db==true)
                                  echo "<option selected value='$i'>$kelas[$i]</option>";
                                else
                                  echo "<option value='$i'>$kelas[$i]</option>";
                              } 
                            }
                          echo "
                          </select>
                        </td>
                        <td>".(($d['label_system']) ? $kelas[$d['label_system']] : '')."</td>
                      </tr>";
                      $tweets[]=$d;
                $no++;
              }
              ?>
              </tbody>
          </table><br>  
      <input type="submit" name="btnTesting" class="btn btn-theme" value="Testing" />
      </form>
    </div><!--/content-panel -->
  </div><!-- /col-md-12 -->
</div><!-- /row -->

<?php
  if(isset($_POST['btnTesting'])){
      $tweets_baru =array();

      foreach($_POST['tweet'] as $i=>$post) { 
          $id_tweet_bersih = $_POST['id_tweet_bersih'][$i];
          $label = $_POST['label'][$i];
          $sql_k = mysql_query("select * from tweet_nbc where id='$id_tweet_bersih'");
          $hitung_jml_data = mysql_num_rows($sql_k);
          //kalo blm pernah di masukan ke training nbc, masukan data baru
          if($hitung_jml_data < 1) {
            $q = mysql_query("insert into tweet_nbc (id, label) values ('$id_tweet_bersih','$label')");
          } else {
            //kalo udah pernah di masukan ke training nbc, update data
            mysql_query("update tweet_nbc set label='".$label."' 
            where id='$id_tweet_bersih' ");
          }
          $tweets_baru[] = array('tweet'=>$post,'label'=>$label,'id'=>$id_tweet_bersih);
      }

      if($cek_tweet>0){
         $tweet_bersih=pre_processing(array('tweet'=>$text_tweet)); //preprocessing tweet

         //pengambilan repons tweet berdsarkan kelas identifikasi // kelas 1,2,3,4
         if($tweet_bersih!=""){ //jika bukan RT
            //print_r($tweet_bersih);

            ######Naive Bayes Classifier######
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
    
            foreach($data_testing as $i=>$tt) {
                $score[$i]=array(1=>$prior[1], // prior pujian
                                 2=>$prior[2], // prior keluhan
                                 3=>$prior[3], // prior follow
                                 4=>$prior[4]); // prior unknown
                
                $score[$i]['text'] = $tt['text'];
                foreach($tt['text'] as $t){
                    foreach ($kataunik as $key=>$d) {
                      if(isset($d['word'])){
                        if($d['word']==$t){
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
                ######rocchio######
                $kata_d['word'] = explode(' ',strtolower($tweets_baru));
                //$kata_d['kelas'] = $label;
                //$kata_d['id_tweet_nbc'] = 1;//$id_tweet_nbc; 
                $cek = klasifikasi_rocchio($kata_d);
                if($cek==1){ //umum
                   $id_tweet_respon=4;
                }else{
                   $id_tweet_respon=3;
                }
                ######rocchio###### 
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
                  
                // d($text_tweet_mention_json_decode);

                mysql_query("update tweets set label='$kelas',id_tweetresponse='$id_tweet_respon' 
                            where id='$id_tweet' ");  

            } else {
              // klo kelas 4 (unknown)
              mysql_query("update tweets set label='$kelas' 
                where id='$id_tweet' ");              
            }

         }else{
            //tweet bersih kosong alias RT
            echo "<div class='alert alert-info' style='margin-top: 35px;'>Tweet merupakan RETWEET (RT) maka tidak direspons</div>";
         }
       

      }else{

          $sql = mysql_query("select id from tweets where id='$id_tweet' ");
          $cek_tweet = mysql_num_rows($sql);

          if($cek_tweet<1){ 
            //Simpan tweet 
            $date=date_create($tweet_baru->created_at);
            $tweet_date = date_format($date,"Y/m/d H:i:s");
            mysql_query("insert into tweets (id,tweet,username,tweet_date) 
                values ('".$tweet_baru->id_str."','".$tweet_baru->text."',
                    '".$tweet_baru->user->screen_name."','$tweet_date') ");
            $tweet_bersih=pre_processing($text_tweet);
          }
      }
    }

  

     function klasifikasi_rocchio($kata_d=array()){ 

      $sqllatih = mysql_query("select tb.id id_tweet_bersih, tb.tweet, tr.label, tn.id id_tweet_nbc
                                from clean_tweet tb left join tweet_nbc tn on tb.id=tn.id
                                join tweet_rocchio tr on tn.id=tr.id
                                where tb.data_status='training' and tn.label='2'"
                              );
                    
      $N = mysql_num_rows($sqllatih);

      //load tf, idf, tfidfp training
      // $sql_tfidf = mysql_query("select * from rocchio_tfidf");
      $sql_tfidf = mysql_query("select a.id, a.class as kelas, a.d, a.tf, a.idf, a.tf_idf, b.word from rocchio_tfidf a, rocchio_word b WHERE a.word = b.id");
      $TF = array();
      $IDF = array();
      $TFIDF = array();
      $semua_kata = array();
      while($tfidf_data = mysql_fetch_array($sql_tfidf)){
        $TF[$tfidf_data['word']][$tfidf_data['kelas']][$tfidf_data['d']]  = $tfidf_data['tf'];
        $IDF[$tfidf_data['word']]= $tfidf_data['idf'];
        $TFIDF[$tfidf_data['word']][$tfidf_data['kelas']][$tfidf_data['d']] 
          = $tfidf_data['tf_idf']; 

        if(!array_key_exists($tfidf_data['word'], $semua_kata)) $semua_kata[$tfidf_data['word']] = array();
      }

      // $semua_kata = array();
      foreach ($kata_d['word'] as $v1) {
          if(!array_key_exists($v1, $semua_kata)) $semua_kata[$v1] = array();
      }
      //semua kata
      // $TF = array();
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

      // Hitung TF
      foreach($semua_kata as $key=>$val){
         //load semua dokumen
        $i=$N+1; 
        $TF[$key]['kelas_uji'][$i]=0;
        foreach($kata_d['word'] as $k){
          if($key==$k){
              $TF[$key]['kelas_uji'][$i] ++; 
          }
        }  
      }

      // Hitung IDF
      // foreach($TF as $index1=>$tf){
      //    $df=0;
      //     foreach($tf as $ft2){
      //        if($ft2 >0 ) $df++;
      //     }
      //    $IDF[$index1] = log10($N/$df) ; 
      // }
      foreach($TF as $index1=>$tf){
         $df=0;
         foreach($tf as $idx=>$ft1){
            if($idx != 'kelas_uji'){
              foreach($ft1 as $ft2){
                 if($ft2 > 0 ) $df++;
              }
            }
         }
         $IDF[$index1] = log10(@($N/$df)) ; 
      }
      // $TFIDF = array();
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
      // ddd($Normalisasi_TFIDF);
      
      // $sql_centroid = mysql_query("select * from rocchio_centroid");
      // $CUuji = array(1=>0,0);
      // while($c=mysql_fetch_array($sql_centroid)){
      //   //ngecek klo kata uji sudah ada di data training
      //   if(array_key_exists($c['kata'], $Normalisasi_TFIDF )){
      //     $CUuji[1] += pow($c['cUmum'] - $Normalisasi_TFIDF[$c['kata']][0] , 2);
      //     $CUuji[2] += pow($c['cSopir'] - $Normalisasi_TFIDF[$c['kata']][0], 2);
      //   }
      // }

      // $sql_centroid = mysql_query("select * from rocchio_centroid");
      $sql_centroid = mysql_query("select a.cUmum, a.cSopir, b.word from rocchio_centroid a, rocchio_word b WHERE a.word = b.id");
      $CUuji = array(1=>0,0);
      while($c=mysql_fetch_array($sql_centroid)){
        //ngecek klo kata uji sudah ada di data training
        if(array_key_exists($c['word'], $Normalisasi_TFIDF )){
          $CUuji[1] += pow($c['cUmum'] - $Normalisasi_TFIDF[$c['word']]['kelas_uji'][$i]  , 2);
          $CUuji[2] += pow($c['cSopir'] - $Normalisasi_TFIDF[$c['word']]['kelas_uji'][$i] , 2);
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

?>