<!--<h3><i class="fa fa-angle-right"></i> Clean Tweets</h3>-->
<div class="row">  
  <div class="col-sm-12">
     <div class="showback">
        <h4><i class="fa fa-angle-right"></i> Clean Tweets</h4>
          <form action="" method='post'>
            <table id="tabel1_search" class="ui celled table" cellspacing="0" width="100%" >  
                <thead>
                <tr>
                  <th>No</th>
                  <th>Tweet</th>
                  <th>Data Status</th>
                </tr>
                </thead>
                <tbody>
                <?php
 
                $sql = mysql_query("select id,tweet, data_status from clean_tweet");
                $no=1;
                $tweets = array();
                $status = array(1=>"training", "testing");
                while($d = mysql_fetch_array($sql)){
                  echo "<tr>
                          <td>$no</td>
                          <td>$d[tweet]
                            <input type='hidden' name='id[]' value='$d[id]' />
                          </td>
                          <td>
                            <select name='data_status[]'>";
                              foreach ($status as $i=>$k){
                                if($status[$i]==$d['data_status'])
                                  echo "<option selected value='$status[$i]'>$status[$i]</option>";
                                else
                                  echo "<option value='$status[$i]'>$status[$i]</option>";
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
            </table><br>
            <input type="submit" name="btnSubmit" class="btn btn-theme" value="Submit" />
          </form> 
    </div>
     
  </div>  



<?php 

  if(isset($_POST['btnSubmit'])){    
      $data_status = $_POST['data_status'];
      foreach($_POST['id'] as $i=>$id){
         mysql_query("update clean_tweet set data_status='$data_status[$i]' 
          where id='$id'");  
      }
      exit("<script>alert('The Data Successfully Created');location='?page=$_GET[page]'</script>");
      
  }
?>