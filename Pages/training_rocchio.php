<h3><i class="fa fa-angle-right"></i> Training Rocchio</h3>
<div class="row">
  <div class="col-md-12">
    <div class="showback">
      <form action="" method='post'>
        <h4><i class="fa fa-angle-right"></i> Data Training</h4>
        <table id="tabel1" class="display" cellspacing="0" width="100%" >
          <thead>
            <tr>
              <th>No</th>
              <th>Tweet</th>
              <th>Clean Tweet</th>
              <th>Class</th>
            </tr>
          </thead>
          <tbody>
          <?php

            /*
              menampilkan tweet hasil join tabel tweet bersih 
                dan tweet_nbc yg statusnya berlabel 2 / keluhan
            */
            $sql = mysql_query("select t1.tweet tweet_kotor, t1.id id_tweet_kotor, tb.id id_tweet_bersih, tb.tweet, tr.label, tn.id id_tweet_nbc
                                from tweets t1 left join clean_tweet tb on t1.id=tb.id
                                left join tweet_nbc tn on tb.id=tn.id
                                left join tweet_rocchio tr on tn.id=tr.id
                                where tb.data_status='training' and tn.label='2'
                              ");

            $no=1;
            $kelas = array(1=>"Keluhan Umum",2=> "Keluhan Sopir");
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
                        $dr_db =false;
                        foreach ($kelas as $i=>$k){
                            if($i==$d['label']){
                              $dr_db = true;
                              echo "<option selected value='$i'>$kelas[$i]</option>";
                            }else{
                              if($i==2 && $dr_db==false)
                                echo "<option selected value='$i'>$kelas[$i]</option>";
                              else
                                echo "<option value='$i'>$kelas[$i]</option>";
                            } 
                        }
                        echo "
                        </select>
                      </td>
                      </tr>";
              $no++;
            }
          ?>
          </tbody>
        </table>
        <br>
        <input type="submit" name="btnTraining" class="btn btn-theme" value="Training" />
      </form> 
    </div><!--/showback-->
  </div><!--/col-md-12-->
</div><!--/row-->



<?php
    require 'Lib/kint/Kint.class.php';
  if(isset($_POST['btnTraining'])){
    

    $tweets = array();
    foreach ($_POST['tweet'] as $i => $post) {
      $id_tweet_nbc = $_POST['id_tweet_nbc'][$i];
      $label = $_POST['label'][$i];
      // cek ditabel rocchio ada ga id nya
      $sql_k = mysql_query("select * from tweet_rocchio 
                            where id_tweet_nbc='$id_tweet_nbc'");
      $hitung_jml_data = mysql_num_rows($sql_k);
      // klo ga ada input ke tabel tweet_rocchio
      if($hitung_jml_data < 1){
        mysql_query("insert into tweet_rocchio
                    values ('".$id_tweet_nbc."','".$label."','')");
        $sql_k = mysql_query("select max(id) maxid from tweet_rocchio");
        // $dt = mysql_fetch_array($sql_k); 
      }else{
          $dt = mysql_fetch_array($sql_k); 
          mysql_query("update tweet_rocchio
           set label='".$label."' where id_tweet_nbc='$id_tweet_nbc' ");
      }
       $tweets[]=array('tweet'=>$post,'label'=>$label);
    } 

    $d = $tweets;
    // hitung jumlah dokumen dalam setiap kelas
    $jml_document_c = array(1=>0,2=>0);

    $kata_d=array();
    foreach($d as $k=>$dd){
        $kata_d[$k]['word'] = explode(' ',strtolower($dd['tweet']));
        //$kata_d[$k]['kata']->kata pada dokumen ke k
        $kata_d[$k]['kelas'] = $dd['label'];
        if(isset($jml_document_c[$dd['label']])) $jml_document_c[$dd['label']] ++;
    }

    // kata_d = [
    //   {
    //     kata: ['sopir','ramah'],
    //     kelas: 1
    //   },
    //   {
    //     kata: ['sopir','ngebut'],
    //     kelas: 2
    //   },
    //   .
    //   .
    //   {}
    // ]

    $semua_kata = array();    
    foreach ($kata_d as $v) {
      foreach($v['word'] as $v1){
        if(!array_key_exists($v1, $semua_kata)) $semua_kata[$v1] = array(); //menampung semua kata yang ada pada seluruh tweet. kata yang sama tidak ditampung lagi. 
      }
    }

    // semua_kata = [];
    // !semua_kata['sopir'] || semua_kata['sopir'] = []
    //  |
    //  v
    // semua_kata = {
    //   sopir: [],
    //   ramah: [],
    //   ngebut: []
    // }


    //Perhitungan TF
    $TF = array();
    foreach($semua_kata as $key=>$val){//load semua dokumen        
      $i=1;
      foreach ($kata_d as $value1) {
        $TF[$key][$value1['kelas']][$i]=0;
        foreach($value1['word'] as $k){
          if($key==$k){
            $TF[$key][$value1['kelas']][$i] ++; 
          }
        } 
      $i++;    
      }
    }

    // TF = []
    // TF[sopir][1][1] = 0, sopir == $key,
    // klo sama -> TF['sopir'][1][1] = 1; karena ++
    // TF = {
    //   sopir: {
    //     1: [1,3,0] <-- jumlah kemunculan kata sopir di tiap tweet, panjang array sebanyak jumlah tweet
    //     2: [1,1]
    //   },
    //   ramah,
    //   ngebut
    // }
    
    //Perhitungan IDF
    $N = count($d);//jumlah all dokumen
    foreach($TF as $index1=>$tf){
       $df=0;//df @ jml doc yg mengandung kata t
       foreach($tf as $ft1){
          foreach($ft1 as $ft2){
             if($ft2 >0 ) $df++;
          }
       }
       $IDF[$index1] = log10($N/$df) ; 
    }

    // $IDF = {
    //   sopir: ?,
    //   ramah: ?
    // }


    $TFIDF=array();
    $Panjang_Vektor=array();
    
    foreach($semua_kata as $key=>$val){
        $i=1;
        foreach ($kata_d as $value1) {
           $TFIDF[$key][$value1['kelas']][$i]=$TF[$key][$value1['kelas']][$i] * $IDF[$key];
           if(isset($Panjang_Vektor[$i])){
              $Panjang_Vektor[$i] += ($TFIDF[$key][$value1['kelas']][$i]*$TFIDF[$key][$value1['kelas']][$i]);
           }else{
              $Panjang_Vektor[$i] = ($TFIDF[$key][$value1['kelas']][$i]*$TFIDF[$key][$value1['kelas']][$i]);
           }
           $i++;    
        }
    }

    // tfidf^2    
    foreach ($Panjang_Vektor as $key1=>$pv) {
       $Panjang_Vektor[$key1] = sqrt($pv);
    }


    $Normalisasi_TFIDF = array();
    foreach($semua_kata as $key=>$val){
        $i=1; 
        foreach ($kata_d as $value1) {
           $Normalisasi_TFIDF[$key][$value1['kelas']][$i] 
            = $TFIDF[$key][$value1['kelas']][$i] / $Panjang_Vektor[$i];
           $i++;    
        }
    }
 
    $Centroid = array();
    $Kelas = array(1,2);
    
    // echo "<pre>";
    // print_r($kata_d );
    // print_r($Normalisasi_TFIDF );
    // exit;
    //memanggil kelas yg isinya 1 dan 2.
    foreach($Kelas as $c){ 
      //mengambil nilai tfidf yg sudah dinormalisasi berdasarkan kata di setiap kelas pada document
      foreach ($Normalisasi_TFIDF as $kata => $kelas) {
         //mengambil nilai kata di semua dokumen pada kelas tertentu, dan dijumlahkan.
         //echo "dDc = ";
        if(isset($kelas[$c])){  
          foreach ($kelas[$c] as $key_d => $normtfidf) {/*$d1 adalah normalisasi tfidf*/
            if(!isset($Sigma_normtfidf[$kata][$c])) $Sigma_normtfidf[$kata] = array($c=>null);
            if(isset($Sigma_normtfidf[$kata])) $Sigma_normtfidf[$kata][$c] += $normtfidf;
            else $Sigma_normtfidf[$kata][$c] = $normtfidf; //$Sigma_tfidf adalah jumlah tfidf seluruh dokumen dalam setiap kelas
            // echo $Sigma_normtfidf[$kata][$c]." | ";
          }
        }
        // echo "".$Sigma_normtfidf[$kata];
        // echo "<br/>";

        $Dc = $jml_document_c[$c]; //jumlah dokument pada kelas tertentu
        // echo "Dc = ".$Dc."<br/>";
        // ddd($Sigma_normtfidf[$kata][$c]);
        $Centroid[$c][$kata] = (1/ abs($Dc) ) * $Sigma_normtfidf[$kata][$c]; //hitung centroid kata pada kelas.
        //echo "Cen = ".$Centroid[$c][$kata]."<br/>";
         
      }
    }
    // d($Normalisasi_TFIDF);
    // d($Sigma_normtfidf);
    // d($Centroid);

    
  //  echo "<pre>";
   /* print_r($TF);
    print_r($IDF);*/
//    print_r($TFIDF);

 //   exit;
    //simpan tf, idf, tfidf
        
    mysql_query("TRUNCATE `rocchio_tfidf`");
    foreach ($TF as $kata=>$kelas) {
      $idf = $IDF[$kata];
      foreach($kelas as $k1=>$d1){
        foreach($d1 as $d2=>$tf){
          $kata = $kata;
          $kelas = $k1;
          $d2 = $d2;
          $tf = $tf;
          $tfidf = $TFIDF[$kata][$k1][$d2]; 
          mysql_query("insert into rocchio_tfidf values
             ('','$kata','$kelas','$d2','$tf','$idf','$tfidf')");
        }
      }
    }

    //exit;

      //hapus data di tabel centroid 
      mysql_query("TRUNCATE `rocchio_centroid`");
      foreach($Centroid[1] as $kata=>$c1){
        mysql_query("insert into rocchio_centroid (word,cUmum,cSopir) values ('$kata','".$Centroid[1][$kata]."','".$Centroid[2][$kata]."') ");
      }

      ?>

<div class="row">
  <div class="col-md-6">
    <div class="showback">
      <form action="" method='post'>
        <h4><i class="fa fa-angle-right"></i> Centroid Kelas</h4>
        <table id="tabel1" class="display" cellspacing="0" width="100%" >
          <thead>
          <tr>
            <th>No</th>
            <th>Kata</th>
            <th>Keluhan Umum</th>
            <th>Keluhan Sopir</th>
          </tr>
          </thead>
          <tbody>
            <?php

              $sqlcentroid = mysql_query("select * from rocchio_centroid");
              $no=1;
              while($dcentroid = mysql_fetch_array($sqlcentroid)){
                echo "<tr>
                        <td>$no</td>
                        <td>$dcentroid[word]</td>
                        <td>$dcentroid[cUmum]</td>
                        <td>$dcentroid[cSopir]</td>
                      </tr>";
                $no++;
              }            
            ?>
          </tbody>
        </table>
        <br>
      </form> 
    </div><!--/showback-->
  </div><!--/col-md-12-->
</div><!--/row-->
      <?php

  }
?>