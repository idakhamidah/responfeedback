<?php
session_start();
if(!isset($_SESSION['username'])){
   exit("<script>alert('Silahkan login');location='index.php'</script>");
}

ini_set('display_errors', 1); //untuk hilangin error edit 0
ini_set('display_startup_errors', 1); //untuk hilangin error edit 0
error_reporting(E_ALL); //untuk hilangin error di komentarin

?>
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
    <link href="Theme/assets//css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="Theme/assets//font-awesome/css/font-awesome.css" rel="stylesheet" />
        
    <!-- Custom styles for this template -->
    <link href="Theme/assets//css/style.css" rel="stylesheet">
    <link href="Theme/assets//css/style-responsive.css" rel="stylesheet">
  </head>
  <body>
  <section id="container" >
      <!--header start-->
      <header class="header black-bg">
              <!-- <div class="sidebar-toggle-box">
                  <div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
              </div> -->
            <!--logo start-->
            <a href="index.html" class="logo"><b>Respon Feedback Tweet EXPRESS GROUP</b></a>

            <div class="top-menu">
            	<ul class="nav pull-right top-menu">
                    <li><a class="logout" href="logout.php">Logout</a></li>
            	</ul>
            </div>
        </header>
      <!--header end-->
      
      <!-- **********************************************************************************************************************************************************
      MAIN SIDEBAR MENU
      *********************************************************************************************************************************************************** -->
      <!--sidebar start-->
      <aside>
          <div id="sidebar"  class="nav-collapse ">
              <!-- sidebar menu start-->
              <ul class="sidebar-menu" id="nav-accordion">
                  <li>
                      <a href="home.php?page=feed_back_tweet">
                          <i class="fa fa-cogs"></i>
                          <span>Feedback Tweet</span>
                      </a>
                  </li>

                  <li class="sub-menu">
                      <a href="javascript:;" >
                          <i class="fa fa-cogs"></i>
                          <span>Preprocessing</span>
                      </a>
                      <ul class="sub">
                         <li>
                              <a href="home.php?page=download_tweet">
                                  <span>Download tweet</span>
                              </a>
                         </li> 
                         <li>
                              <a href="home.php?page=pre_processing">
                                  <span>Preprocessing</span>
                              </a>
                          </li> 
                         <li>
                              <a href="home.php?page=tweet_bersih">
                                  <span>Clean Tweets</span>
                              </a>
                          </li>
                      </ul>
                  </li>


                  <li class="sub-menu">
                      <a href="javascript:;" >
                          <i class="fa fa-cogs"></i>
                          <span>Training</span>
                      </a>
                      <ul class="sub">
                          <li><a  href="home.php?page=training_nbc">NBC</a></li>
                          <li><a  href="home.php?page=training_rocchio">Rocchio</a></li>
                      </ul>
                  </li>

                  <li class="sub-menu">
                      <a href="javascript:;" >
                          <i class="fa fa-cogs"></i>
                          <span>Testing</span>
                      </a>
                      <ul class="sub">
                          <li><a  href="home.php?page=testing_nbc">NBC</a></li>
                          <li><a  href="home.php?page=testing_rocchio">Rocchio</a></li>
                      </ul>
                  </li>

                  <li class="sub-menu">
                      <a href="javascript:;" >
                          <i class="fa fa-cogs"></i>
                          <span>Data Master</span>
                      </a>
                      <ul class="sub">
                          <li><a  href="home.php?page=stopword">Stopword</a></li>
                          <li><a  href="home.php?page=replace_word">Raplace Word</a></li>
                          <li><a  href="home.php?page=dictionary">Dictionary</a></li>
                          <li><a  href="home.php?page=tweet">Tweet</a></li>
                          <li><a  href="home.php?page=tweet_response">Tweet Response</a></li>
                      </ul>
                  </li>
              </ul>
              <!-- sidebar menu end-->
          </div>
      </aside>
      <!--sidebar end-->
      
      <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->
      <!--main content start-->
      <section id="main-content">
          <section class="wrapper site-min-height">
              <?php
                 include "Config/koneksi.php";
                $page = "home";
                if(isset ($_GET['page'])){
                  $page = $_GET['page'];
                }
                $file_page = "Pages/".$page.".php";
                if(file_exists($file_page)){
                  include $file_page;
                }else
                  echo "Halaman tidak ditemukan";
              ?>	
		      </section><! --/wrapper -->
      </section><!-- /MAIN CONTENT -->

      <!--main content end-->
      <!--footer start-->
      <footer class="site-footer">
          <div class="text-center">
              Express Group
              <a href="blank.html#" class="go-top">
                  <i class="fa fa-angle-up"></i>
              </a>
          </div>
      </footer>
      <!--footer end-->
  </section>

    <!-- js placed at the end of the document so the pages load faster -->
    <script src="Theme/assets//js/jquery.js"></script>
    <script src="Theme/assets//js/bootstrap.min.js"></script>
    <script src="Theme/assets//js/jquery-ui-1.9.2.custom.min.js"></script>
 
    <script src="Theme/assets//js/jquery.ui.touch-punch.min.js"></script>
    <script class="include" type="text/javascript" src="Theme/assets//js/jquery.dcjqaccordion.2.7.js"></script>
    <script src="Theme/assets//js/jquery.scrollTo.min.js"></script>
    <script src="Theme/assets//js/jquery.nicescroll.js" type="text/javascript"></script>

    <link rel="stylesheet" type="text/css" href="Lib/DataTables-1.10.11/media/css/jquery.dataTables.css"> 
    <script type="text/javascript" language="javascript" src="Lib/DataTables-1.10.11/media/js/jquery.dataTables.js">
    </script>
    <script type="text/javascript" language="javascript" src="Lib/DataTables-1.10.11/examples/resources/syntax/shCore.js">
    </script>
    <script type="text/javascript" language="javascript" src="Lib/DataTables-1.10.11/examples/resources/demo.js">
    </script>
    <script type="text/javascript" language="javascript" class="init">
    
      $(document).ready(function() {
        $('#tabel1,#tabel2,#tabel3,#tabel4').DataTable( {
          "scrollY":        "300px",
          "scrollCollapse": true,
          "paging":         false,
          "ordering": false,
          "info":     false,
          "bFilter" : false,
          "searching" : false
        } );

        $('#tabel1_search').DataTable( {
          "scrollY":        "300px",
          "scrollCollapse": true,
          "paging":         false,
          "ordering": false,
          "info":     false,
          "bFilter" : false,
          "searching" : true
        } ); 

        $('#tabel_paging').DataTable( {
          "scrollY":        "300px",
          "paging":         true,
          "ordering": true,
          "bFilter" : true,
          "searching" : true
        } );
      } );

    </script>


    <!--common script for all pages-->
    <script src="Theme/assets//js/common-scripts.js"></script>

    <!--script for this page-->
    
    <script>
        //custom select box

        $(function(){
            $('select.styled').customSelect();
        });
        
    </script>

  </body>
</html>
