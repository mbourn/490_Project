<?php include "../cgi-bin/auth.php"; ?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<?php 
  //start();
  //include 'https://mbourn/cgi-bin/auth.php'; 
  if($_GET["action"]=='runstart'){
    start();
  } 
?>
<button onclick="window.location='https://mbourn.com/authorized/authorized.php?action=runstart';">Start</button>

</body>
</html>


