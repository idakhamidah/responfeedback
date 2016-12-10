<h3><i class="fa fa-angle-right"></i> Training NBC</h3>
<div class="row">
 <div class="col-md-12">
  	  <div class="content-panel">
        <form action="" method='post'>
          <table class="table table-hover">
  	  	  <h4><i class="fa fa-angle-right"></i> Training NBC</h4>
  	  	  <hr>
              <thead>
              <tr>
                  <th>No</th>
                  <th>Tweet</th>
                  <th>Label</th>
              </tr>
              </thead>
              <tbody>
            	<?php

            	$sql = mysql_query("select tweet, label from tweet_nbc_latih");
              if(mysql_num_rows($sql) <1){
                $sql = mysql_query("select tweets, label from tweet_bersih");
              }
            	$no=1;
            	$tweets = array();
              $kelas = array(1=>"Pujian", "Keluhan", "Follow", "Unknown");
              while($d = mysql_fetch_array($sql)){
                echo "<tr>
                        <td>$no</td>
                        <td>$d[tweet]
                          <input type='hidden' name='tweet[]' value='$d[tweet]' />
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
                       </tr>";
                       $tweets[]=$d;
                $no++;
              }
            	?>
              </tbody>
          </table>
          <input type="submit" name="btnTraining" class="btn btn_primary" value="Training" />
        </form> 
  	  </div>
  </div>
</div>

<?php 
  if(isset($_POST['btnTraining'])){
    mysql_query("TRUNCATE 'tweet_nbc_latih'");
    //proses pelatihan nbc
    $N = count($_POST['tweet']);
    $Nc = array();
    $prior = array();
    $text_Concat = array();

    foreach($_POST['tweet'] as $i=>$post) {
      //Simpan data dan label ke tabel
      mysql_query("insert into tweet_nbc_latih values ('','$post','".$_POST['label'][$i]."')");
      //set $Nc
      if(array_key_exists(trim($_POST['label'][$i]), $Nc)) $Nc[trim($_POST['label'][$i])]++;
      else $Nc[trim($_POST['label'][$i])]=1;

      if (array_key_exists(trim($_POST['label'][$i]),$text_Concat)){
        $text_Concat[trim($_POST['label'][$i])]=$text_Concat[trim($_POST['label'][$i])]." ".$post;
      }else{
        $text_Concat[trim($_POST['label'][$i])] = $post;
      }
    }
    //hitung prior
    foreach ($Nc as $c => $c_val) {
      $prior[$c]=$Nc[$c]/$N;
      mysql_query("insert into nbc_classpriors value ('','$c','".$prior[$c]."')");
    }
    $B = 0;
    mysql_query("TRUNCATE 'nbc_kataunik'");
    foreach ($text_Concat as $key => $value) {
      $token = explode(" ", $value);
      $fs_token = array();
      foreach ($token as $v1) {
        if (!empty($v1)) {
          if(array_key_exists($v1, $fs_token)) $fs_token[$v1]++;
          else{
            $B = $B+1;
            $fs_token[$v1]=1;

            //simpan kata unik
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
      <div class="col-md-12">
        <div class="content-panel">
          <form action="" methode='post'>
            <table class="table table-hover">
            <h4><i class="fa fa-angle-right"></i> Jumlah Kemunculan kata perkelas</h4>
            <hr>
                <thead>
                <tr>
                    <th>No</th>
                    <th>Kata</th>
                    <th>pPujian</th>
                    <th>pKeluhan</th>
                    <th>pFollow</th>
                    <th>pUnknown</th>
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
           </form> 
        </div>
    </div>
  </div>

  <div class="row">
        <div class="col-md-12">
          <div class="content-panel">
            <form action="" methode='post'>
              <table class="table table-hover">
              <h4><i class="fa fa-angle-right"></i> Probabilitas prior</h4>
              <hr>
                  <thead>
                  <tr>
                      <th>No</th>
                      <th>Kata</th>
                      <th>pPujian</th>
                      <th>pKeluhan</th>
                      <th>pFollow</th>
                      <th>pUnknown</th>
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
                                <td>".hitung_prob($Tct,1,$d['kata'],$B)."</td>
                                <td>".hitung_prob($Tct,2,$d['kata'],$B)."</td>
                                <td>".hitung_prob($Tct,3,$d['kata'],$B)."</td>
                                <td>".hitung_prob($Tct,4,$d['kata'],$B)."</td>
                              </tr>";  

                          //update Probabilitas
                          mysql_query("update set 
                                pPujian ='".hitung_prob($Tct,1,$d['kata'],$B)."',
                                pPujian ='".hitung_prob($Tct,2,$d['kata'],$B)."',
                                pPujian ='".hitung_prob($Tct,3,$d['kata'],$B)."',
                                pPujian ='".hitung_prob($Tct,4,$d['kata'],$B)."'
                                where id_kata='$d[id_kata]'
                          ");                    
                      }
                    ?>
                  </tbody>
              </table>
             </form> 
          </div>
        </div>
  </div>
  <?php  
  }

function jml_kemunculan_kata_perkelas($Tct, $c,$k1){
  $Tct_c_v = 0;
  if(isset($Tct[$c][$k1])) $Tct_c_v =$Tct[$c][$k1];
  return $Tct_c_v;
}
function hitung_prob($Tct,$c,$k1,$B){
  $condprob_tc = 0;
  if(array_key_exists($c, $Tct)){
    $tc_=0;
    foreach ($Tct[$c] as $j) {
      $tc_ += $j;
    }
    $Tct_c_v=0;
    if (isset($Tct[$c][$k1])) $Tct_c_v = $Tct[$c][$k1];
    $condprob_tc=($Tct_c_v + 1)/($tc_+$B);  
  }
  return $condprob_tc;
}