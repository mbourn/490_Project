<!DOCTYPE html>
<html>
<head>
  <?php 
    require "cgi-bin/auth.php";

      //error_reporting(E_ALL);
      //ini_set('display_errors','On');

    if( isset($_GET['id'])){
      $last_id = $_GET['id'];
      if( isset($_GET['action']) && $_GET['action'] == 'all'){
        make_all($_GET['id']);
      }
    }elseif( isset($_POST['last_id'])){
      $last_id = $_POST['last_id'];
      echo $last_id;
    }else{
      die("ERROR: User Id not passed<br>");
    }

    if( isset($_POST['multi_submit'])){
      if( !isset($_POST['contact'])){
        echo '<p id="multi_error">You must select at least one contact</p>';
      }else{
        make_multi_set($_POST['contact']);
      }
    }
     
    $conn = create_db_connection();
    $sql = "SELECT * FROM Network WHERE c_of = $last_id ORDER BY l_name ASC";
    $result = mysqli_query($conn, $sql);

  ?>
<link rel="stylesheet" href="main.css">
</head>
<body>
<header>
  <?php render_header(); ?>
</header>
<main>
<div id="multi_div">
<div id="multi_div_expl">
  Select contacts from the list below.  When you are happy with the set of contacts, click on Create to create and download a zip file containing all of those contacts' vCards.  Additionally, click on Edit to manually change any contact's information.
</div>
  <form action="multi_vcard.php" method="POST" id="multi_form">
    <?php render_multi_div($result, $last_id); ?>
    <td colspan=4><div id="multi_cnt">
      <input type="hidden" name="last_id" value="<?php echo $last_id; ?>">
      <input id="multi_sub_btn" class="btn" type="submit" name="multi_submit" value="Create">
    </div></td>
  </form>
  </table>
</div>


</main>
<footer>
<?php render_footer(); ?>
</footer>
</body>
</html>
