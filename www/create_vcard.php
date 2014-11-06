<!DOCTYPE html>
<html>
<head>
  <?php include "cgi-bin/auth.php"; ?>
</head>
<body>
<header>
  <h1>Create the vCards</h1>
</header>
<main>
  <p><b>last id = <?php echo intval($_GET['id']) ?></b></p>
  <div><?php 
    $test = " this, is a test ";
    echo $test;
    $test = strtr($test, ",", " ");
    echo $test;
    ?>
  </div>
  
  
  <?php
    error_reporting(E_ALL);
    ini_set('display_errors','On');


    $last_id = intval($_GET['id']);
    create_all($last_id);
  ?>

</body>
</html>
