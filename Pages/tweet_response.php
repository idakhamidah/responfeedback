<?php
$aksi = "";
if(isset($_GET['aksi'])) $aksi = $_GET['aksi'];
switch($aksi){
	default : 
	?>
		<h3><i class="fa fa-angle-right"></i> tweet response</h3>
			<a href="<?php echo "?page=$_GET[page]&aksi=create";?>" class="btn btn-theme">Create</a>
		    <div class="row">
		      <div class="col-md-12 mt">
		      	<div class="content-panel">
		              <table table id="tabel_paging" class="display" cellspacing="0" width="100%" border="0">
		      	  	  <h4><i class="fa fa-angle-right"></i> tweet response</h4>
		      	  	  <hr>
		                  <thead>
		                  <tr>
		                      <th>No</th>
		                      <th width="500px">Tweet Response</th>
		                      <th>Label</th>
		                      <th>Action</th> 
		                  </tr>
		                  </thead>
		                  <tbody>
		                    <?php 
		                      $sql = mysql_query("select * from tweet_response");
		                      $no=1; 
		                      while($d = mysql_fetch_array($sql)){
		                        echo "<tr>
		                                <td>$no</td>
		                                <td>$d[tweetresponse]</td>
		                                <td>$d[label]</td>
		                                <td>
		                                	<a class='btn btn-warning' href='?page=$_GET[page]&aksi=update&id=$d[id]'> Update </a>
		                                	<a class='btn btn-danger' 
		                                		onclick='return confirm(\"Apakah Anda Yakin?\")' 
		                                		href='?page=$_GET[page]&aksi=delete&id=$d[id]'> Delete </a>
		                                </td>
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
	
	case "create" : 
		?>
		<h3><i class="fa fa-angle-right"></i> Form Create Tweet Response</h3>
          	<div class="row mt">
          		<div class="col-lg-12">
                  <div class="form-panel">
                  	  <h4 class="mb"><i class="fa fa-angle-right"></i> Form Create Tweet Response</h4>
                      <form class="form-horizontal style-form" method="post" action="">
                          <div class="form-group">
                              <label class="col-sm-2 col-sm-2 control-label">Tweet</label>
                              <div class="col-sm-10">
                                  <input type="text" name="txttweetresponse" required="required" class="form-control">
                                  <br>
                              </div>
                              <label class="col-sm-2 col-sm-2 control-label">Label</label>
                              <div class="col-sm-10">
                                  <input type="text" name="txtlabel" required="required" class="form-control">
                                  <br>
                              </div>
                          </div>
                          <div class="form-group">
                              <div class="col-lg-10">
                              	<button type="submit" name="btnSave" class="btn btn-theme">Save</button>
                          	  </div>
                          </div>
                      </form>
                  </div>
          		</div>    	
          	</div> 
		<?php
			if(isset($_POST['btnSave'])){
				$s = mysql_query("insert into tweet_response values ('','$_POST[txttweetresponse]','$_POST[txtlabel]') ");
		        if($s){
		        	exit("<script>alert('The Data Successfully Created');location='?page=$_GET[page]'</script>");
                }else 
                  exit("<script>alert('The Data isn't Successfully Created');location='?page=$_GET[page]&aksi=$_GET[aksi]'</script>");
             
			}
			break;
	
	case "update" : 
		$s = mysql_query("select * from tweet_response where id='$_GET[id]'");
		$d = mysql_fetch_array($s);
		?>
		<h3><i class="fa fa-angle-right"></i> Form Update</h3>
          	<div class="row mt">
          		<div class="col-lg-12">
                  <div class="form-panel">
                  	  <h4 class="mb"><i class="fa fa-angle-right"></i> Form Update</h4>
                      <form class="form-horizontal style-form" method="post" 
                      	action="<?php echo "?page=$_GET[page]&aksi=$_GET[aksi]&id=$_GET[id]"; ?>">
                          <div class="form-group">
                              <label class="col-sm-2 col-sm-2 control-label">Tweet Response</label>
                              <div class="col-sm-10">
                                  <input type="text" name="txttweetresponse" value="<?php echo $d['tweetresponse'];?>" required="required" class="form-control" />
                                  <br>
                              </div>

                              <label class="col-sm-2 col-sm-2 control-label">Tweet</label>
                              <div class="col-sm-10">
                                  <input type="text" name="txtlabel" value="<?php echo $d['label'];?>" required="required" class="form-control" />
                                  <br>
                              </div>

                              
                           </div>
                  			</div> 
			                  <div class="form-group">
						          <div class="col-lg-10">
						          	<button type="submit" name="btnUpdate" class="btn btn-theme">Update</button>
						      	  </div>
			                  </div>
                      </form>
                  </div>
          		</div>    	
          	</div> 
		<?php
			if(isset($_POST['btnUpdate'])){
				$s = mysql_query("update tweet_response set 
						tweetresponse='$_POST[txttweetresponse]', label='$_POST[txtlabel]'
						where id='$_GET[id]' "); 
		        if($s){
		        	exit("<script>alert('The Data Successfully Updated');location='?page=$_GET[page]'</script>");
                }else 
                  exit("<script>alert('The Data isn't Successfully Updated');location='?page=$_GET[page]&aksi=$_GET[aksi]'</script>");
             
			}
			break; 

	case "delete" : 
		$s = mysql_query("delete from tweet_response where id='$_GET[id]' ");
        if($s){
        	exit("<script>alert('The Data Successfully Deleted');location='?page=$_GET[page]'</script>");
        }else 
          exit("<script>alert('The Data isn't Successfully Deleted');location='?page=$_GET[page]&aksi=$_GET[aksi]'</script>");
             
		break;

}
?>			