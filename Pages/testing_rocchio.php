<?php require_once "Lib/kint/Kint.class.php"; ?>
<h3><i class="fa fa-angle-right"></i> Testing Rocchio</h3>
<div class="row">
 <div class="col-sm-12">
    <div class="showback">
      <h4><i class="fa fa-angle-right"></i> Data Tweet</h4>
      <form action="" method="post">
      <table id="tabel1" class="display" cellspacing="0" width="100%" >
          <thead>
          <tr>
              <th>No</th>
              <th>Tweet</th>
              <th>Clean Tweet</th>
              <th>Class</th>
              <th>Class(System)</th>
          </tr>
          </thead>
          <tbody>
            <?php 
              $sql = mysql_query("select t1.tweet tweet_kotor, t1.id id_tweet_kotor, tb.id id_tweet_bersih, tb.tweet, tr.label, tn.id id_tweet_nbc
                                from tweets t1 left join clean_tweet tb on t1.id=tb.id
                                left join tweet_nbc tn on tb.id=tn.id
                                left join tweet_rocchio tr on tn.id=tr.id
                                where tb.data_status='testing' and tn.label='2'
                                ");
            // $sql = mysql_query("SELECT tb.id, tb.tweet, tn.id id_tweet_nbc 
            //                     FROM tweet_bersih tb, tweet_nbc tn 
            //                     WHERE status_data='testing' 
            //                     AND tn.label='2'
            //                   ");
                    // left join tweet_nbc tn on tb.id=tn.id_tweet_bersih
              $no=1;
              $tweets = array();
              $kelas = array(1=>"Keluhan Umum", "Keluhan Sopir");

              while($d = mysql_fetch_array($sql)){
                echo "<tr>
                        <td>$no</td>
                        <td>$d[tweet_kotor]
                          <input type='hidden' name='tweet_kotor[]' value='$d[tweet_kotor]' />
                          <input type='hidden' name='id_tweet_kotor[]' value='$d[id_tweet_kotor]' />
                        </td>
                        <td>$d[tweet]
                          <input type='hidden' name='tweet[]' value='$d[tweet]' />
                          <input type='hidden' name='id_tweet_nbc[]' value='$d[id_tweet_nbc]' />
                        </td>
                        <td>
                          <select name='label[]'>";
                          foreach ($kelas as $i=>$k){
                            if($i==$d['label'])
                              echo "<option selected value='$i'>$kelas[$i]</option>";
                            else
                              echo "<option value='$i'>$kelas[$i]</option>";
                          }
                          echo "
                          </select>
                        </td>
                        <td>".$kelas[$d['label_sistem']]."</td>
                       </tr>";      
                       $tweets[]=$d; 
                $no++;
              } 
            ?>
          </tbody>
      </table>
      <input type="submit" name="btnTesting" class="btn btn_primary" value="Testing" />
      </form>
    </div><!--/content-panel -->
  </div><!-- /col-sm-12 -->
</div>


<?php
  if(isset($_POST['btnTesting'])){  
      $jml_document_c = array(1=>0,2=>0);
      $kata_d=array();
      $benar = 0; 


      $sqllatih = mysql_query("select 
                                tb.id id_tweet_bersih, tb.tweet, tr.label,
                                tn.id id_tweet_nbc
                                from clean_tweet tb
                                left join tweet_nbc tn on tb.id=tn.id
                                join tweet_rocchio tr on tn.id=tr.id
                                where tb.data_status='training' and tn.label='2'"
                              );

      $N = mysql_num_rows($sqllatih);

      //load tf, idf, tfidfp training
      $sql_tfidf = mysql_query("select * from rocchio_tfidf");
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

      foreach ($_POST['tweet'] as $i => $post) {
        $id_tweet_nbc = $_POST['id_tweet_nbc'][$i];
        $label = $_POST['label'][$i];
        $sql_k = mysql_query("select * from tweet_rocchio 
            where id='$id_tweet_nbc'");

        $hitung_jml_data = mysql_num_rows($sql_k);
        if($hitung_jml_data < 1){
          mysql_query("insert into tweet_rocchio
             values ('".$id_tweet_nbc."','".$label."','')");
        }else{
            mysql_query("update tweet_rocchio
             set label='".$label."' where id='$id_tweet_nbc' ");
        }

        echo "<br>";
        echo "Data testing = "; print_r ($post);  

        $kata_d['word'] = explode(' ',strtolower($post));
        $kata_d['kelas'] = $label;
        $kata_d['id'] = $id_tweet_nbc;
        
        $cek = klasifikasi_rocchio($kata_d,$N,$semua_kata, $TF, $IDF, $TFIDF);
        // ddd($cek, $kata_d);
        
        if($cek == $label){
           $benar++;
        }
        $jml_document_c[$label] ++; 
        mysql_query("update tweet_rocchio
           set label_system='".$cek."' where 
           id='".$id_tweet_nbc."' "); 

      } 
      $akurasi = @ ( ($benar / count($_POST['tweet'])) * 100 ); 
      echo "<div class='alert alert-info'>
                <b>Akurasi Rocchio $akurasi %</b>

            </div>";
  }
  
  function klasifikasi_rocchio($kata_d=array(),$N, $semua_kata, $TF, $IDF, $TFIDF){  
      foreach ($kata_d['word'] as $v1) { 
        if(!array_key_exists($v1, $semua_kata)) $semua_kata[$v1] = array();
      }

      //Perhitungan TF
      
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

      //Perhitungan IDF
      //$N = $N;  
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
      foreach($semua_kata as $key=>$val){
         $i=$N+1;  
         $Normalisasi_TFIDF[$key]['kelas_uji'][$i] 
          = $TFIDF[$key]['kelas_uji'][$i] / $Panjang_Vektor[$i]; 
      }

      // d($Normalisasi_TFIDF); 
      // Normalisasi_TFIDF = [
      //   taksi = [
      //     1 =[
      //       1=0,89899
      //       2=0
      //     ]
      //     2 =[
      //       3=0,89899 ----> 3 dan 4 adalah id tweet rocchio
      //       4=0
      //     ]
      //   ]
      // ]
       
      $sql_centroid = mysql_query("select * from rocchio_centroid");
      $CUuji = array(1=>0,0);
      while($c=mysql_fetch_array($sql_centroid)){
        //ngecek klo kata uji sudah ada di data training
        if(array_key_exists($c['word'], $Normalisasi_TFIDF )){
          $CUuji[1] += pow($c['cUmum'] - $Normalisasi_TFIDF[$c['word']]['kelas_uji'][$i] , 2);
          $CUuji[2] += pow($c['cSopir'] - $Normalisasi_TFIDF[$c['word']]['kelas_uji'][$i] , 2);
        }
      }

      foreach($CUuji as $k=>$v):
        $CUuji[$k] = sqrt($v);
      endforeach;

      //kata yg tidak ada di data training
      //$kata_normal_uji = array();
      // foreach($Normalisasi_TFIDF as $kata=>$ntf){
      //     $ada = false;
      //     while($c1=mysql_fetch_array($sql_centroid)){
      //         ddd($c1);
      //        if($c1['kata']==$kata){
      //           $ada=true;
      //        }
      //     }
      //     if($ada==false){
      //       $CUuji[1] += pow(0 - $Normalisasi_TFIDF[$kata]['kelas_uji'][$i]  , 2);
      //       $CUuji[2] += pow(0 - $Normalisasi_TFIDF[$kata]['kelas_uji'][$i] , 2);
      //       $kata_normal_uji[$kata] = $ntf;
      //     }
      // }
      
      
      echo "<br>";
      echo "Nilai Jarak Dokumen dengan centroid keluhan umum adalah = "; print_r($CUuji[1]); 
      echo "<br>";
      echo "Nilai Jarak Dokumen dengan centroid keluhan sopir adalah = "; print_r($CUuji[2]);
      echo "<br><br>";

      if($CUuji[1] < $CUuji[2]){
        return 1;
      }
      return 2;

    }


?>
