<?php
$aksi = "";
if(isset($_GET['aksi'])) $aksi = $_GET['aksi'];
switch($aksi){
	default : 
	?>
		<h3><i class="fa fa-angle-right"></i> Dictionary</h3>
			<a href="<?php echo "?page=$_GET[page]&aksi=create";?>" class="btn btn-theme">Create</a>
		    <div class="row">
		      <div class="col-md-12 mt">
		      	<div class="content-panel">
		              <table table id="tabel_paging" class="display" cellspacing="0" width="100%" border="0">
		      	  	  <h4><i class="fa fa-angle-right"></i> List Dictionary</h4>
		      	  	  <hr>
		                  <thead>
		                  <tr>
		                      <th>No</th>
		                      <th>Root</th>
		                      <th>Root Type</th>
		                      <th>Action</th> 
		                  </tr>
		                  </thead>
		                  <tbody>
		                    <?php 
		                      $sql = mysql_query("select * from vocabs");
		                      $no=1; 
		                      while($d = mysql_fetch_array($sql)){
		                        echo "<tr>
		                                <td>$no</td>
		                                <td>$d[katadasar]</td>
		                                <th>$d[tipe_katadasar]</th> 
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
                              <label class="col-sm-2 col-sm-2 control-label">Root</label>
                              <div class="col-sm-10">
                                  <input type="text" name="txtroot" required="required" class="form-control">
                                  <br>
                              </div>
                              <label class="col-sm-2 col-sm-2 control-label">Root Type</label>
                              <div class="col-sm-10">
                                
                                <select name="txtroottype" required="required" class="form-control">
	                        		<?php
	                        			$type = array("Adjektiva","Adverbia","Interjeksi"
	                        				,"Konjungsi","Nomina","Preposisi","Pronomina"
	                        				,"Verba","Lain-lain");
	                        			foreach($type as $t){
	                        				echo "<option value='$t'>$t</option>";
	                        			} 
	                        		?>
                                </select>

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
				$s = mysql_query("insert into vocabs values ('','$_POST[txtroot]','$_POST[txtroottype]') ");
		        if($s){
		        	exit("<script>alert('The Data Successfully Created');location='?page=$_GET[page]'</script>");
                }else 
                  exit("<script>alert('The Data isn't Successfully Created');location='?page=$_GET[page]&aksi=$_GET[aksi]'</script>");
             
			}
			break;
	
	case "update" : 
		$s = mysql_query("select * from vocabs where id='$_GET[id]'");
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
                              <label class="col-sm-2 col-sm-2 control-label">Root</label>
                              <div class="col-sm-10">
                                  <input type="text" name="txtroot" value="<?php echo $d['katadasar'];?>" required="required" class="form-control" />
                                  <br>
                              </div>
                              <label class="col-sm-2 col-sm-2 control-label">Root Type</label>
                              <div class="col-sm-10">
                                
                                <select name="txtroottype" required="required" class="form-control">
	                        		<?php
	                        			$type = array("Adjektiva","Adverbia","Interjeksi"
	                        				,"Konjungsi","Lain-lain","Nomina","Preposisi","Pronomina"
	                        				,"Verba");
	                        			foreach($type as $t){
	                        				if($d['tipe_katadasar']==$t)
			                        			echo "<option value='$t' selected>$t</option>";
	                        				else
	                        					echo "<option value='$t'>$t</option>";
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
				$s = mysql_query("update vocabs set 
						katadasar='$_POST[txtroot]',tipe_katadasar='$_POST[txtroottype]'
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