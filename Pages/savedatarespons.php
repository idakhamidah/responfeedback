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
            </tr>
            </thead>
            <tbody>
              <?php
              $sql = mysql_query("select t.tweet tweet_kotor, t.username username, tb.id id_tweet_kotor, tb.id id_tweet_bersih, 
                                  tb.tweet, tn.label label_man, tn.label_system
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
                        <input type='hidden' name='label_man[]' value='$d[label_man]' />
                        <input type='hidden' name='username[]' value='$d[username]' />
                      </tr>";
                      $tweets[]=$d;
                $no++;
              }
              ?>
              </tbody>
          </table><br>  
      <input type="submit" name="btnSave" class="btn btn-theme" value="Save" />
      </form>
    </div><!--/content-panel -->
  </div><!-- /col-md-12 -->
</div><!-- /row -->
<?php
    if(isset($_POST['btnSave'])){
        foreach($_POST['tweet_kotor'] as $i=>$post) { 
            $id_tweet = $_POST['id_tweet_kotor'][$i];
            $tweet = $_POST['tweet_kotor'][$i];
            $username = $_POST['username'][$i];
            $label = $_POST['label_man'][$i];
            
            // d($_POST['username']);
            // ddd($_POST['label_man']);
            // ddd($_POST['id_tweet_kotor'][0]);
            // ddd($_POST['id_tweet_kotor'][0]);

            mysql_query("insert into tweettest values ('$id_tweet','$tweet', '$username', '$label', '', '')");
            d(mysql_query("insert into tweettest values ('$id_tweet','$tweet', '$username', '$label', '', '')"));
                       
        }      
    }
?>