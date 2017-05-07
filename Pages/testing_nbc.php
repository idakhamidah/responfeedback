<?php require "Lib/kint/Kint.class.php"; ?>
<!--<h3><i class="fa fa-angle-right"></i> Testing NBC</h3>-->
<div class="row">
 <div class="col-sm-12">
    <div class="showback">
      <h4><i class="fa fa-angle-right"></i> Testing Naive Bayes Classifier</h4>
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
                            foreach ($kelas as $i=>$k){
                              if($i==$d['label']) {
                                echo "<option selected value='$i'>$kelas[$i]</option>";
                              } elseif($i==2) {
                                  echo "<option selected value='$i'>$kelas[$i]</option>";
                              } else {
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
          //kalo pernah di masukan ke training nbc, masukan data baru
          if($hitung_jml_data < 1) {
            $q = mysql_query("insert into tweet_nbc (id, label) values ('$id_tweet_bersih','$label')");
          } else {
            //kalo udah pernah di masukan ke training nbc, update data
            mysql_query("update tweet_nbc set label='".$label."' 
            where id='$id_tweet_bersih' ");
          }
          $tweets_baru[] = array('tweet'=>$post,'label'=>$label,'id'=>$id_tweet_bersih);
      }

      // $_POST['tweet']=>[
      //   "sopir ramah"
      //   "balap tidaknyaman sopir tidak sopan"
      //   "nyaman ramah sopir"
      // ]

      // $_POST['id_tweet_bersih']=>[2,3,9]
      // $_POST['label']=>[1,2,1]



      //echo "<pre>";
      //print_r($tweets_baru);
      //exit;

      //ambil data kelas prior
      $no=0; 
      //kata unik
      $sql_kataunik = mysql_query("select * from nbc_kataunik");
      $kataunik = array();
      while($d_unik = mysql_fetch_array($sql_kataunik)){
        $kataunik[] = $d_unik;
      }

      $alldata_testing = array();
      foreach($tweets_baru as $tw){
        $alldata_testing[] = array('label'=>$tw['label'],
                                  "text" => explode(" ", $tw['tweet']) ,
                                  "id_tweet_bersih" => $tw['id']
                                  );
      }
?>

        <?php
        $klasifikasi = array("1"=>"pujian","2"=>"keluhan","3"=>"follow","4"=>"unknown");
             
          $sql_prior = mysql_query("select * from nbc_priorclass");
             $prior = array();
             while($dprior = mysql_fetch_array($sql_prior)){
                $prior[$dprior['class']] = $dprior['prior'];
             }
            $no=1;
            $benar = 0;
            
            // d($prior[2]);
            // [1: pujian, 2: keluhan, 3: follow, 4: unknown]
            foreach($alldata_testing as $i=>$data_testing) {                
                $score[$i]=array(1=>$prior[1], // pujian
                                 2=>$prior[2], // keluhan
                                 3=>$prior[3], // follow
                                 4=>$prior[4]); // unknown
                $score[$i]['text'] = $data_testing['text'];
                // print_r($data_testing['text']);
                echo "Data test : ";
                foreach($data_testing['text'] as $t){
                  echo "$t ";
                    foreach ($kataunik as $key=>$d) {
                      if($d['word']==$t){
                        // d($d['word'], $d['pKeluhan']);
                        $score[$i][1] = $score[$i][1] * $d['pPujian'];
                        $score[$i][2] = $score[$i][2] * $d['pKeluhan'];// d('nilai sebelum dikali', $score[$i][2]);
                        $score[$i][3] = $score[$i][3] * $d['pFollow'];// d('nilai setelah dikali', $score[$i][2]);
                        $score[$i][4] = $score[$i][4] * $d['pUnknown'];                         
                        $no++;
                      }
                    }
                  }
                  
                  echo "<br> Probabilitas Posterior kelas Pujian = ";
                  print (number_format($score[$i][1],25,",","."));//sprintf ('%e',$score[$i][1]);
                  echo "<br> Probabilitas Posterior kelas Keluhan = ";
                  print (number_format($score[$i][2],25,",","."));
                  echo "<br> Probabilitas Posterior kelas Follow = ";
                  print (number_format($score[$i][3],25,",","."));
                  echo "<br> Probabilitas Posterior kelas Unknown = ";
                  print (number_format($score[$i][4],25,",","."));
                  echo "<br>";

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

                  if($kelas==$data_testing['label']) $benar++;

                  mysql_query("update tweet_nbc set label_system='".$kelas."' 
                     where id='".$data_testing['id_tweet_bersih']."' ");

                  echo "Hasil klasifikasi : $klasifikasi[$kelas] <br><br>";
            }
        ?>

      <?php

      $total_data_testing = count($alldata_testing);
      echo "<div class='alert alert-info'>Akurasi = ". (( $benar/$total_data_testing) * 100 ) . "%</div>"; 
      
  }
?>