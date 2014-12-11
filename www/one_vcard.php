<!DOCTYPE html>
<head>
<?php 
  require "cgi-bin/auth.php"; 
  //error_reporting(E_ALL);
  //ini_set('display_errors','On');

  //var_dump($_POST);

  // Set variables
  if( isset($_GET['id'])){
    $last_id = $_GET['id'];
  }elseif( isset($_POST['last_id'])){
    $last_id = $_POST['last_id'];
  }else{
    echo "Last_id was not passed";
  }
  $search_result=NULL;
//echo $_POST['select_btn'];

  // Handle the form submission
  if( isset($_POST['select_btn'])){
    $post_action = $_POST['select_btn'];
  }elseif( isset($_POST['search_btn'])){
    $post_action = $_POST['search_btn'];
  }elseif( isset($_POST['edit_btn'])){
   // echo "ERROR: POST was malformed.<br>";
  }

  // Take appropriate action  
  switch($post_action){
    // If the user clicked on edit, perform edit functions
    case "Edit":
      header('HTTP/1.1 307 Temporary Redirect');
      header('Location: https://mbourn.com/edit.php');
      //echo "EDIT";
    break;

    // If the user clicked on Download, perform download functions
    case "Download":
      if( isset($_POST['c_id'])){
        $cid = $_POST['c_id'];
      }else{
        echo "Contact id not in POST";
      } 
      make_one($cid);
      delete_files();
    break;
    
    // If the user clicked on Search, perform search functions 
    case "Search":
      if( isset($_POST['c_name'])){
        $c_name = $_POST['c_name'];
      }else{
        echo "POST did not contain the contact's name<br>";
      }
      $search_result = find_contact($c_name, $last_id);
    break;
  }
?>
<link rel="stylesheet" href="main.css">
</head>
<html>
<body>
<?php 
  $conn = create_db_connection();
  $sql = "SELECT f_name, l_name, c_id FROM Network WHERE c_of = $last_id ORDER BY 2 ASC";
  $result = mysqli_query($conn, $sql);
  mysqli_close($conn);
?>
<header>
  <?php render_header(); ?>
</header>
<div id="search">
<div id="get_one_select_div">
  <div id="select_expl" style="text-align: center;">
    Please select the contact from the dropdown box to either edit or download.  
  </div>
  <form action="https://<?php echo $_SERVER['SERVER_NAME']; ?>/one_vcard.php" method="POST" id="select_form">
  <input type="hidden" name="return_addr" value="https://<?php echo $_SERVER['SERVER_NAME']; ?>/one_vcard.php?id=<?php echo $last_id ?>">
    <input type="hidden" name="last_id" value="<?php echo $last_id; ?>">
    <div id="select_p">
      <select name="c_id">
      <?php 
        while($row = mysqli_fetch_array($result)){
          $f_name = $row['f_name'];
          $l_name = $row['l_name'];
          $c_id = $row['c_id'];
          echo '<option value="'.$c_id.'">'.$f_name.' '.$l_name.'</option>';
        }
      ?>
      </select>
    </div>
    <div id="select_buttons_p">
      <input class="btn" type="submit" name="select_btn" value="Edit" id="select_edit">
      <input class="btn" type="submit" name="select_btn" value="Download" id="select_dl">
    </div>
  </form>
</div>

<?php render_search_div(isset($_POST['search_btn']), $search_result, $last_id); ?>
</div></div></div>
</body>
<footer>
<?php render_footer(); ?>
</footer>
</html>
