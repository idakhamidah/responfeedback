<?php
$aksi = "";
if(isset($_GET['aksi'])) $aksi = $_GET['aksi'];
switch($aksi){
	default : 
			?>
			<h3><i class="fa fa-angle-right"></i> Stopwords</h3>

				<a href="<?php echo "?page=$_GET[page]&aksi=tambah";?>" class="btn btn-theme">Tambah</a>

			    <div class="row">
			      <div class="col-md-12 mt">
			      	<div class="content-panel">
			              <table table id="tabel_paging" class="display" cellspacing="0" width="100%" >
			      	  	  <h4><i class="fa fa-angle-right"></i> Stopwords</h4>
			      	  	  <hr>
			                  <thead>
			                  <tr>
			                      <th>#</th>
			                      <th>stopword</th>  
			                      <th>Aksi</th> 
			                  </tr>
			                  </thead>
			                  <tbody>
			                    <?php 
			                      $sql = mysql_query("select * from stopwords");
			                      $no=1; 
			                      while($d = mysql_fetch_array($sql)){
			                        echo "<tr>
			                                <td>$no</td>
			                                <td>$d[stopword]</td> 
			                                <td>
			                                	<a class='btn btn-warning' href='?page=$_GET[page]&aksi=edit&id=$d[id]'> Edit </a>
			                                	<a class='btn btn-danger' 
			                                		onclick='return confirm(\"Apakah Anda Yakin?\")' 
			                                		href='?page=$_GET[page]&aksi=hapus&id=$d[id]'> Hapus </a>
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
	
	case "tambah" : 
		?>
		<h3><i class="fa fa-angle-right"></i> Form Tambah</h3>
          	<div class="row mt">
          		<div class="col-lg-12">
                  <div class="form-panel">
                  	  <h4 class="mb"><i class="fa fa-angle-right"></i> Form Tambah</h4>
                      <form class="form-horizontal style-form" method="post" action="">
                          <div class="form-group">
                              <label class="col-sm-2 col-sm-2 control-label">stopword</label>
                              <div class="col-sm-10">
                                  <input type="text" name="txtstopword" required="required" class="form-control">
                              </div>
                          </div> 
                          <div class="form-group">
                              <div class="col-lg-10">
                              	<button type="submit" name="btnSimpan" class="btn btn-theme">Simpan</button>
                          	  </div>
                          </div>
                      </form>
                  </div>
          		</div>    	
          	</div> 
		<?php
			if(isset($_POST['btnSimpan'])){
				$s = mysql_query("insert into stopwords values ('','$_POST[txtstopword]') ");
		        if($s){
		        	exit("<script>alert('Data berhasil disimpan');location='?page=$_GET[page]'</script>");
                }else 
                  exit("<script>alert('Data tidak berhasil disimpan');location='?page=$_GET[page]&aksi=$_GET[aksi]'</script>");
             
			}
			break;
	
	case "edit" : 
		$s = mysql_query("select * from stopwords where id='$_GET[id]'");
		$d = mysql_fetch_array($s);
		?>
		<h3><i class="fa fa-angle-right"></i> Form Edit</h3>
          	<div class="row mt">
          		<div class="col-lg-12">
                  <div class="form-panel">
                  	  <h4 class="mb"><i class="fa fa-angle-right"></i> Form Edit</h4>
                      <form class="form-horizontal style-form" method="post" 
                      	action="<?php echo "?page=$_GET[page]&aksi=$_GET[aksi]&id=$_GET[id]"; ?>">
                          <div class="form-group">
                              <label class="col-sm-2 col-sm-2 control-label">stopword</label>
                              <div class="col-sm-10">
                                  <input type="text" name="txtstopword" value="<?php echo $d['stopword'];?>" required="required" class="form-control">
                              </div>
                          </div> 
                          <div class="form-group">
                              <div class="col-lg-10">
                              	<button type="submit" name="btnSimpan" class="btn btn-theme">Simpan</button>
                          	  </div>
                          </div>
                      </form>
                  </div>
          		</div>    	
          	</div> 
		<?php
			if(isset($_POST['btnSimpan'])){
				$s = mysql_query("update stopwords set 
						stopword='$_POST[txtstopword]' 
						where id='$_GET[id]' "); 
		        if($s){
		        	exit("<script>alert('Data berhasil diubah');location='?page=$_GET[page]'</script>");
                }else 
                  exit("<script>alert('Data tidak berhasil diubah');location='?page=$_GET[page]&aksi=$_GET[aksi]'</script>");
             
			}
			break; 

	case "hapus" : 
		$s = mysql_query("delete from stopwords where id='$_GET[id]' ");
        if($s){
        	exit("<script>alert('Data berhasil dihapus');location='?page=$_GET[page]'</script>");
        }else 
          exit("<script>alert('Data tidak berhasil dihapus');location='?page=$_GET[page]&aksi=$_GET[aksi]'</script>");
             
		break;

}
?>