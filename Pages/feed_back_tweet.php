<?php
include "Pages/reload_tweet.php";

$aksi = "";
if(isset($_GET['aksi'])) $aksi = $_GET['aksi'];
switch($aksi){
	default : 
	?>
		<h3><i class="fa fa-angle-right"></i> Feed back tweet</h3>
			 <div class="row">
		      <div class="col-md-12 mt">
		      	<div class="content-panel">
		              <table table id="tabel_paging" class="display" cellspacing="0" width="100%" border="0">
		      	  	  <h4><i class="fa fa-angle-right"></i> Feed back tweet</h4>
		      	  	  <hr>
		                  <thead>
		                  <tr>
		                      <th width="10px">No</th>
		                      <th width="200px">Tweet</th>
		                      <th width="10px">Label</th> 
		                      <th>Respon</th> 
		                  </tr>
		                  </thead>
		                  <tbody>
		                    <?php 
		                      // $sql = mysql_query("select 
		                      // 		tweets.tweet tweet_kotor,
		                      // 		tweet_bersih.tweet tweet_bersih,
		                      // 		tweet_response.tweetresponse,tweet_response.label
		                      //   from tweets 
		                      // 	join tweet_bersih on tweet_bersih.id_tweet=tweets.id
		                      // 	left join tweet_response on tweets.id_tweet_respon=tweet_response.id");
		                    $sql = mysql_query("SELECT tweets.tweet tweet_kotor, 
		                    					tweet_response.tweetresponse, tweet_response.label
												FROM tweets, tweet_response
												WHERE tweets.id_tweet_respon = tweet_response.id
												ORDER BY tweets.id DESC");
		                      $no=1; 
		                      while($d = mysql_fetch_array($sql)){
		                        echo "<tr>
		                                <td>$no</td>
		                                <td>$d[tweet_kotor]</td>
		                                <td>$d[label]</td> 
		                                <td>$d[tweetresponse]</td> 
		                              </tr>"; 
		                        $no++;
		                      } 
		                    ?> 
		                  </tbody>
		              </table>
		      	  </div> 
		      </div> 
			</div>
			<?php
			break;

}
?>