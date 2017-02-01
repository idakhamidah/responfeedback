<?php 
  include "Lib/function_pre_processing.php";
?> 
<!--<h3><i class="fa fa-angle-right"></i> Preprocessing</h3>-->
<div class="row">
 <div class="col-sm-12">
      <div class="showback">
          <h4><i class="fa fa-angle-right"></i> Preprocessing</h4>
            <form action="" method='post'>
              <table id="tabel1_search" class="ui celled table" cellspacing="0" width="100%" >
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
                      //mysql_query("TRUNCATE `tweet_bersih`");
                      $sql = mysql_query("select id,tweet_date,tweet,username from tweets");
                      $no=1;
                      $tweets = array();
                      
                      while($d = mysql_fetch_array($sql)){
                          $tweets[] = $d;
                          $pre="";
                          if(isset($_POST['btnPre'])){ 
                            $pre = pre_processing($d);
                            echo "<tr>
                                  <td>$no</td>
                                  <td>$d[tweet_date]</td>
                                  <td>$d[tweet]</td>
                                  <td>".$pre."</td>
                                </tr>";
                            if($pre!=""){
                              $s = mysql_query("select id_tweet from tweet_bersih where id_tweet='$d[id]' ");
                              $cek_data = mysql_num_rows($s); //apakah sudah pernah disimpan
                              if($cek_data <1 ) { //kalo kurang dari 1, brrti blm pernah disimpan
                                mysql_query("insert into clean_tweet values ('$d[id]','$pre','training')");
                              }else{
                                mysql_query("update clean_tweet set tweet='".$pre."' where id_tweet='$d[id]'");
                              }
                            } 
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
                <button type="submit" name="btnPre" class="btn btn-theme">Preproses</button>
            </form>  
      </div><!--/showback-->
  </div><!-- /col-md-12 -->
</div><!-- /row -->
