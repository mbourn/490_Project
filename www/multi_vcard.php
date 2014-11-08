<!DOCTYPE html>
<html>
<head>
  <?php 
    require "cgi-bin/auth.php";

      error_reporting(E_ALL);
      ini_set('display_errors','On');

    var_dump($_POST);

    if( isset($_GET['id'])){
      $last_id = $_GET['id'];
      if( isset($_GET['action']) && $_GET['action'] == 'all'){
        make_all($_GET['id']);
      }
    }elseif( isset($_POST['last_id'])){
      $last_id = $_POST['last_id'];
    }else{
      echo "ERROR: User Id not passed<br>";
    }

    if( isset($_POST['multi_submit'])){
      if( !isset($_POST['contact'])){
        echo 'You must select at least one contact<br>';
      }else{
        make_multi_set($_POST['contact']);
      }
    }
     
    $conn = create_db_connection();
    $sql = "SELECT * FROM Network WHERE c_of = $last_id ORDER BY l_name ASC";
    $result = mysqli_query($conn, $sql);
    var_dump($result);

  ?>
</head>
<body>
<header>
 <h1>Multi</h1>
</header>
<main>
<div id="multi_div_expl">
  Select contacts from the list below.  When you are happy with the set of contacts, click on Create to create and download a zip file containing all of those contacts' vCards.  Additionally, click on Edit to manually change any contact's information.
</div>
<div id="multi_div">
  <form action="multi_vcard.php" method="POST" id="multi_form">
    <?php render_multi_div($result, $last_id); ?>
    <input type="hidden" name="last_id" value="<?php echo $last_id; ?>">
    <input type="submit" name="multi_submit" value="Create">
  </form>
</div>


</main>
</body>
</html>