<h3><i class="fa fa-angle-right"></i> Clean Tweets</h3>
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
                    <th>Status Data</th>
                </tr>
                </thead>
                <tbody>
                <?php
 
                $sql = mysql_query("select id,tweet, status_data from tweet_bersih");
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
                            <select name='status_data[]'>";
                              foreach ($status as $i=>$k){
                                if($status[$i]==$d['status_data'])
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
            </table>
            <input type="submit" name="btnSubmit" class="btn btn_primary" value="Submit" />
          </form> 
    </div>
     
  </div>  



<?php 

  if(isset($_POST['btnSubmit'])){    
      $status_data = $_POST['status_data'];
      foreach($_POST['id'] as $i=>$id){
         mysql_query("update tweet_bersih set status_data='$status_data[$i]' 
          where id='$id'");  
      }
      exit("<script>alert('The Data Successfully Created');location='?page=$_GET[page]'</script>");
      
  }
?>