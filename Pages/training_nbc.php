<?php // require '/Lib/kint/Kint.class.php'; ?>

<h3><i class="fa fa-angle-right"></i> Training NBC</h3>
<div class="row">  
  <div class="col-sm-12">
    <div class="showback">
      <h4><i class="fa fa-angle-right"></i> Tweet Bersih</h4>
        <form action="" method='post'>
          <table id="tabel1_search" class="ui celled table" cellspacing="0" width="100%" >  
            <thead>
            <tr>
              <th>No</th>
              <th>Tweet</th>
              <th>Tweet Bersih</th>
              <!-- <th>Frekuensi Kata</th> -->
              <th>Label</th>
            </tr>
            </thead>
            <tbody>
              <?php
              $sql = mysql_query("select 
                                  tb.id id_tweet_bersih, t.tweet as tweet, tb.tweet as tweet_bersih, tn.label
                                  from tweet_bersih tb
                                  join tweets t on t.id=tb.id_tweet
                                  left join tweet_nbc tn on tb.id=tn.id_tweet_bersih
                                  where tb.status_data='training'"
                                );
              $no=1;
              $tweets = array();
              $kelas = array(1=>"Pujian", "Keluhan", "Follow", "Unknown");
              while($d = mysql_fetch_array($sql)){
                echo "<tr>
                        <td>$no</td>
                        <td>$d[tweet]</td>
                        <td>$d[tweet_bersih]
                          <input type='hidden' name='id_tweet_bersih[]' value='$d[id_tweet_bersih]' />
                          <input type='hidden' name='tweet[]' value='$d[tweet_bersih]' />
                        </td>";
                // echo "<td>";
                //   // buat array baru untuk hitung frekuensi
                //   $fr = array();
                //   // explode per kata, kemudian simpan sebagai $fr_tweet
                //   foreach(explode(" ", $d['tweet_bersih']) as $fr_tweet):
                //     if(!isset($fr[$fr_tweet])) $fr[$fr_tweet] = 0;
                //     $fr[$fr_tweet] += 1;  
                //   endforeach; 

                //   // tampilkan berdasarkan key dan value (key->kata nya, value-> jumlah frekuensi)
                //   foreach($fr as $k=>$v):
                //     echo "$k : $v <br>";
                //   endforeach;
                // echo "</td>";
                echo "<td>
                          <select name='label[]'>";
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
                      </tr>";
                      $tweets[]=$d;
                $no++;
              }
            ?>
            </tbody>
        </table>
        <input type="submit" name="btnTraining" class="btn btn_primary" value="Training" />
      </form> 
    </div><!--/showback-->
  </div><!--/col-sm-12-->  
</div><!--/row-->

<?php 
// require "Lib/kint/Kint.class.php";
  if(isset($_POST['btnTraining'])){
    //proses pelatihan nbc
    $N = count($_POST['id_tweet_bersih']);
    $Nc = array();
    $prior = array();
    $text_Concat = array();

    foreach($_POST['tweet'] as $i=>$post) { 
      $id_tweet_bersih = $_POST['id_tweet_bersih'][$i];
      $label = $_POST['label'][$i];
      $sql_k = mysql_query("select * from tweet_nbc where id_tweet_bersih='$id_tweet_bersih'");
      $hitung_jml_data = mysql_num_rows($sql_k);
      
      //kalo blm pernah dimasukan ke training nbc, masukan data baru
      if($hitung_jml_data < 1)
        mysql_query("insert into tweet_nbc values ('','$id_tweet_bersih','".$label."','')");
      //kalo udah pernah dimasukan ke training nbc, update data
      else
        mysql_query("update tweet_nbc set label='".$label."' 
        where id_tweet_bersih='$id_tweet_bersih' ");
        
      //set $Nc
      if(array_key_exists(trim($label), $Nc)) $Nc[trim($label)]++;
      else $Nc[trim($label)]=1;
      
      if (array_key_exists(trim($label),$text_Concat)){
        $text_Concat[trim($label)]=$text_Concat[trim($label)]." ".$post;
      }else{
        $text_Concat[trim($label)] = $post;
      }
    }

    //hitung prior
    prior ($Nc,$N);
    $B = 0;
    
    mysql_query("TRUNCATE nbc_kataunik");
    foreach ($text_Concat as $key => $value) {
      $token = explode(" ", $value);
      $fs_token = array();
      foreach ($token as $v1) {
        $v1=trim($v1);
        if (!empty($v1)) {
          if(array_key_exists($v1, $fs_token)) $fs_token[$v1]++;
          else{
            $B = $B+1;
            $fs_token[$v1]=1;
            //simpan kata unik
            //nyari kata tertentu di tabel,
            $sql_k = mysql_query("select * from nbc_kataunik where kata='$v1'");
            //hitung brp jml baris data yg di temukan, kalau 0, brrti katanya blm ada :p
            $hitung_jml_data = mysql_num_rows($sql_k);
            if($hitung_jml_data < 1)
              mysql_query("insert into nbc_kataunik values('','$v1','','','','')");
          }
        }
      }
      $Tct[$key]=$fs_token;
    }

   $no=0;
   $kataunik = mysql_query("select * from nbc_kataunik");
?>

<div class="row">  
  <div class="col-sm-12">
    <div class="showback">
      <h4><i class="fa fa-angle-right"></i> Kemunculan kata</h4> 
      <table id="tabel3" class="display" cellspacing="0" width="100%" > 
        <thead>
          <tr>
            <th>No</th>
            <th>Kata</th>
            <th>Pujian</th>
            <th>Keluhan</th>
            <th>Follow</th>
            <th>Unknown</th>
          </tr>
        </thead>
        <tbody>
          <?php
            while ($d=mysql_fetch_array($kataunik)) {
              $no++;
              echo "<tr>
                        <td>$no</td>
                        <td>$d[kata]</td>
                        <td>".jml_kemunculan_kata_perkelas($Tct,1,$d['kata'])."</td>
                        <td>".jml_kemunculan_kata_perkelas($Tct,2,$d['kata'])."</td>
                        <td>".jml_kemunculan_kata_perkelas($Tct,3,$d['kata'])."</td>
                        <td>".jml_kemunculan_kata_perkelas($Tct,4,$d['kata'])."</td>
                    </tr>";
            }
          ?>
        </tbody>
      </table>  
    </div><!--/showback-->
  </div><!--/col-sm-12-->  
</div><!--/row-->

<div class="row">  
  <div class="col-sm-6">
    <div class="showback">
      <h4><i class="fa fa-angle-right"></i> Probabilitas Prior</h4> 
      <table id="tabel2" class="display" cellspacing="0" width="100%" > 
        <thead>
          <tr>
            <th>No</th>
            <th>Kelas</th>
            <th>Prior</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $no=0;
            $probprior = mysql_query("select * from nbc_priorclass");
            while ($d=mysql_fetch_array($probprior)) {
              $no++;
              echo "<tr>
                        <td>$no</td>
                        <td>".$kelas[$d['class']]."</td>
                        <td>".number_format($d['prior'],14,'.',',')."</td>
                    </tr>";
            }
          ?>
        </tbody>
      </table>  
    </div><!--/showback-->
  </div><!--/col-sm-12--> 
</div><!--/row-->

<div class="row">
  <div class="col-sm-12">
    <div class="showback">
      <h4><i class="fa fa-angle-right"></i> Probabilitas Bersyarat</h4>
      <table id="tabel4" class="display" cellspacing="0" width="100%" > 
        <thead>
        <tr>
          <th>No</th>
          <th>Kata</th>
          <th>Pujian</th>
          <th>Keluhan</th>
          <th>Follow</th>
          <th>Unknown</th>
        </tr>
        </thead>
        <tbody>
          <?php
            $no=0;
            $kataunik = mysql_query("select * from nbc_kataunik");
            $B=mysql_num_rows($kataunik);
            while ($d=mysql_fetch_array($kataunik)) {
              $no++;
              echo"<tr>
                      <td>$no</td>
                      <td>$d[kata]</td>
                      <td>".number_format(condprob($Tct,1,$d['kata'],$B), 14, ".",",")."</td>
                      <td>".number_format(condprob($Tct,2,$d['kata'],$B), 14, ".",",")."</td>
                      <td>".number_format(condprob($Tct,3,$d['kata'],$B), 14, ".",",")."</td>
                      <td>".number_format(condprob($Tct,4,$d['kata'],$B), 14, ".",",")."</td>
                    </tr>";  

                //update Probabilitas
                mysql_query("update nbc_kataunik set 
                      pPujian ='".condprob($Tct,1,$d['kata'],$B)."',
                      pKeluhan ='".condprob($Tct,2,$d['kata'],$B)."',
                      pFollow ='".condprob($Tct,3,$d['kata'],$B)."',
                      pUnknown ='".condprob($Tct,4,$d['kata'],$B)."'
                      where id_kata='$d[id_kata]'
                ");                    
            }
          ?>
        </tbody>
      </table> 
    </div>
  </div> 
</div>

<!--</div> -->          
<?php  
  }

function prior ($Nc,$N){
  mysql_query("TRUNCATE nbc_priorclass");
  foreach ($Nc as $c => $c_val) {
    $prior[$c]=$Nc[$c]/$N;
    mysql_query("insert into nbc_priorclass values ('','$c','".number_format($prior[$c],14)."')");
  }
}

function jml_kemunculan_kata_perkelas($Tct, $c, $k1){
  $Tct_c_v = 0;
  if(isset($Tct[$c][$k1])) $Tct_c_v =$Tct[$c][$k1];
  return $Tct_c_v;
}

function condprob($Tct,$c,$kata,$B){
  $condprob_tc = 0;
  if(array_key_exists($c, $Tct)){
    $tc_=0;
    foreach ($Tct[$c] as $j) {
      $tc_ += $j;
    }
    $Tct_c_v=0;
    if (isset($Tct[$c][$kata])) $Tct_c_v = $Tct[$c][$kata];
    $condprob_tc=($Tct_c_v + 1)/($tc_+$B);  
  }
  return $condprob_tc;
}