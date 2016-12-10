<?php
$aksi = "";
if(isset($_GET['aksi'])) $aksi = $_GET['aksi'];
switch($aksi){
	default : 
	?>
		<h3><i class="fa fa-angle-right"></i> Tweet</h3>
			<a href="<?php echo "home.php?page=$_GET[page]&aksi=create";?>" class="btn btn-theme">Create</a>
			<a href="<?php echo "home.php?page=download_tweet";?>" class="btn btn-theme">Download</a>
		    <div class="row">
		      <div class="col-md-12 mt">
		      	<div class="content-panel">
		              <table table id="tabel_paging" class="display" cellspacing="0" width="100%" border="0">
		      	  	  <h4><i class="fa fa-angle-right"></i> Tweet</h4>
		      	  	  <hr>
		                  <thead>
		                  <tr>
		                      <th>No</th>
		                      <th width="750px">Tweet</th>
		                      <th>Label</th>
		                      <th>Action</th> 
		                  </tr>
		                  </thead>
		                  <tbody>
		                    <?php 
		                      $sql = mysql_query("select * from tweets");
		                      $no=1; 
		                      while($d = mysql_fetch_array($sql)){
		                        echo "<tr>
		                                <td>$no</td>
		                                <td>$d[tweet]</td>
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
		<h3><i class="fa fa-angle-right"></i> Form Create Dictionary</h3>
          	<div class="row mt">
          		<div class="col-lg-12">
                    <div class="form-panel">
                  	  <h4 class="mb"><i class="fa fa-angle-right"></i> Form Create</h4>
                      <form class="form-horizontal style-form" method="post" action="">
                          <div class="form-group">
                              <label class="col-sm-2 col-sm-2 control-label">Tweet</label>
                              <div class="col-sm-10">
                                  <input type="text" name="txttweet" required="required" class="form-control">
                                  <br>
                              </div>
                              <label class="col-sm-2 col-sm-2 control-label">Label</label>
                              <div class="col-sm-10">
                                  <input type="text" name="txtlabel" required="required" class="form-control">
                                  <br>
                              </div>
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
				$s = mysql_query("insert into tweets values ('','','$_POST[txttweet]','','','$_POST[txtlabel]') ");
		        if($s){
		        	exit("<script>alert('The Data Successfully Created');location='?page=$_GET[page]'</script>");
                }else 
                  exit("<script>alert('The Data isn't Successfully Created');location='?page=$_GET[page]&aksi=$_GET[aksi]'</script>");
             
			}
			break;
	
	case "update" : 
		$s = mysql_query("select * from tweets where id='$_GET[id]'");
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
                              <label class="col-sm-2 col-sm-2 control-label">Id Tweet</label>
                              <div class="col-sm-10">
                                  <input type="number" name="numtweetid" value="<?php echo $d['tweet_id'];?>" required="required" class="form-control" />
                                  <br>
                              </div>

                              <label class="col-sm-2 col-sm-2 control-label">Tweet</label>
                              <div class="col-sm-10">
                                  <input type="text" name="txttweet" value="<?php echo $d['tweet'];?>" required="required" class="form-control" />
                                  <br>
                              </div>

                              <label class="col-sm-2 col-sm-2 control-label">Username</label>
                              <div class="col-sm-10">
                                  <input type="text" name="txtusername" value="<?php echo $d['username'];?>" required="required" class="form-control" />
                                  <br>
                              </div>
                              <label class="col-sm-2 col-sm-2 control-label">Tweet Date</label>
                              <div class="col-sm-10">
                                  <input type="text" name="txttweetdate" value="<?php echo $d['tweet_date'];?>" required="required" class="form-control" />
                                  <br>
                              </div>
                              <label class="col-sm-2 col-sm-2 control-label">Label</label>
                              <div class="col-sm-10">
                                
                                <select name="labeltweet" required="required" class="form-control">
	                        		<?php
	                        			$label = array("Pujian","Keluhan","Follow"
	                        				,"Unknown");
	                        			foreach($label as $valuelabel){
	                        				if($d['label']==$valuelabel)
			                        			echo "<option value='$valuelabel' selected>$valuelabel</option>";
	                        				else
	                        					echo "<option value='$valuelabel'>$valuelabel</option>";
	                        			} 
	                        		?>
                                </select>
                                
                                  <br>
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
				$s = mysql_query("update tweets set 
						tweet_id='$_POST[numtweetid]', tweet='$_POST[txttweet]', username='$_POST[txtusername]', tweet_date='$_POST[txttweetdate]', label='$_POST[labeltweet]'
						where id='$_GET[id]' "); 
		        if($s){
		        	exit("<script>alert('The Data Successfully Updated');location='?page=$_GET[page]'</script>");
                }else 
                  exit("<script>alert('The Data isn't Successfully Updated');location='?page=$_GET[page]&aksi=$_GET[aksi]'</script>");
             
			}
			break; 

	case "delete" : 
		$s = mysql_query("delete from vocabs where id='$_GET[id]' ");
        if($s){
        	exit("<script>alert('The Data Successfully Deleted');location='?page=$_GET[page]'</script>");
        }else 
          exit("<script>alert('The Data isn't Successfully Deleted');location='?page=$_GET[page]&aksi=$_GET[aksi]'</script>");
             
		break;

}
?>			