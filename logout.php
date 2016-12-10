<?php
	session_start();
	session_destroy();
	exit("<script>location='index.php'</script>");
?>