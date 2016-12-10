<?php
$aksi = "";
if(isset($_GET['aksi'])) $aksi = $_GET['aksi'];
switch($aksi){
	default : 
	?>
		<h3><i class="fa fa-angle-right"></i> Replace Word</h3>
			<a href="<?php echo "?page=$_GET[page]&aksi=create";?>" class="btn btn-theme">Create</a>
		    <div class="row">
		      <div class="col-md-12 mt">
		      	<div class="content-panel">
		              <table table id="tabel_paging" class="display" cellspacing="0" width="100%" border="0">
		      	  	  <h4><i class="fa fa-angle-right"></i> List Replace Word</h4>
		      	  	  <hr>
		                  <thead>
		                  <tr>
		                      <th>No</th>
		                      <th>Username</th>
		                      <th>Password</th>  
		                      <th>Action</th> 
		                  </tr>
		                  </thead>
		                  <tbody>
		                    <?php 
		                      $sql = mysql_query("select * from operator");
		                      $no=1; 
		                      while($d = mysql_fetch_array($sql)){
		                        echo "<tr>
		                                <td>$no</td>
		                                <td>$d[username]</td>
		                                <td>$d[password]</td> 
		                                <td>
		                                	<a class='btn btn-warning' href='?page=$_GET[page]&aksi=update&id=$d[id_operator]'> Update </a>
		                                	<a class='btn btn-danger' 
		                                		onclick='return confirm(\"Apakah Anda Yakin?\")' 
		                                		href='?page=$_GET[page]&aksi=delete&id=$d[id_operator]'> Delete </a>
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
		<h3><i class="fa fa-angle-right"></i> Form Create Operator</h3>
          	<div class="row mt">
          		<div class="col-lg-12">
                  <div class="form-panel">
                  	  <h4 class="mb"><i class="fa fa-angle-right"></i> Create Operator</h4>
                      <form class="form-horizontal style-form" method="post" action="">
                          <div class="form-group">
                              <label class="col-sm-2 col-sm-2 control-label">Username</label>
                              <div class="col-sm-10">
                                  <input type="text" name="txtusername" required="required" class="form-control">
                                  <br>
                              </div>
                              <label class="col-sm-2 col-sm-2 control-label">Password</label>
                              <div class="col-sm-10">
                                  <input type="password" name="passoperator" required="required" class="form-control">
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
				$s = mysql_query("insert into operator values ('','$_POST[txtusername]','$_POST[passoperator]') ");
		        if($s){
		        	exit("<script>alert('Successfully Saved');location='?page=$_GET[page]'</script>");
                }else 
                  exit("<script>alert('Failed Saved');location='?page=$_GET[page]&aksi=$_GET[aksi]'</script>");
             
			}
			break;
	
	case "update" : 
		$s = mysql_query("select * from operator where id_operator='$_GET[id]'");
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
                              <label class="col-sm-2 col-sm-2 control-label">Username</label>
                              <div class="col-sm-10">
                                  <input type="text" name="txtword" value="<?php echo $d['username'];?>" required="required" class="form-control">
                                  <br>
                              </div>
                              <label class="col-sm-2 col-sm-2 control-label">Password</label>
                              <div class="col-sm-10">
                                  <input type="password" name="passoperator" value="<?php echo $d['password'];?>" required="required" class="form-control">
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
				$s = mysql_query("update operator set 
						username='$_POST[txtword]', password='$_POST[passoperator]'
						where id_operator='$_GET[id]' "); 
		        if($s){
		        	exit("<script>alert('The Data successfully updated');location='?page=$_GET[page]'</script>");
                }else 
                  exit("<script>alert('The data isn't successfully updated');location='?page=$_GET[page]&aksi=$_GET[aksi]'</script>");
             
			}
			break; 

	case "delete" : 
		$s = mysql_query("delete from operator where id_operator='$_GET[id]' ");
        if($s){
        	exit("<script>alert('Data berhasil dihapus');location='?page=$_GET[page]'</script>");
        }else 
          exit("<script>alert('Data tidak berhasil dihapus');location='?page=$_GET[page]&aksi=$_GET[aksi]'</script>");
             
		break;

}
?>			