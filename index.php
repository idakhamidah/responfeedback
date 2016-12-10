<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

    <title>Feedback Tweeet Express Group</title>

    <!-- Bootstrap core CSS -->
    <link href="Theme/assets/css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="Theme/assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
        
    <!-- Custom styles for this template -->
    <link href="Theme/assets/css/style.css" rel="stylesheet">
    <link href="Theme/assets/css/style-responsive.css" rel="stylesheet">

    
  </head>
  <body>
	  <div id="login-page">
	  	<div class="container">
		      <form class="form-login" action="" method="post">
		        <h2 class="form-login-heading">sign in now</h2>
		        <div class="login-wrap">
		            <input name="txtUsername" type="text" class="form-control" 
		            	placeholder="User ID" autofocus />
		            <br>
		            <input name="txtPassword" type="password" class="form-control" 
		            	placeholder="Password" />
		            <br>
		            <button name="btnLogin" class="btn btn-theme btn-block" 
		              type="submit"><i class="fa fa-lock"></i> Login</button>
		            <hr>
		        </div>
		      </form>	

		      <?php
		      	if(isset($_POST['btnLogin'])){
		      		include "Config/koneksi.php";
		      		$s = mysql_query("select * from operator where username='$_POST[txtUsername]' and password='$_POST[txtPassword]'");
		      		$n = mysql_num_rows($s);
		      		if($n>0){
		      			session_start();
		      			$d = mysql_fetch_array($s);
		      			$_SESSION['username'] = $d['username'];
		      			$_SESSION['password'] = $d['password'];
		      			exit("<script>alert('Selamat datang');location='home.php?page=feed_back_tweet'</script>");
		      		}else
		      			exit("<script>alert('Username dan password tidak ditemukan');
		      				location='index.php'</script>");
		      	}
		      ?>  	
	  	
	  	</div>
	  </div>
    <script src="Theme/assets/js/jquery.js"></script>
    <script src="Theme/assets/js/bootstrap.min.js"></script>
    
  </body>
</html>
