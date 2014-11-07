<!DOCTYPE html>
<head>
<?php 
  require "cgi-bin/auth.php"; 
  error_reporting(E_ALL);
  ini_set('display_errors','On');

var_dump($_POST);
//echo $_POST['select_btn'];

  if($_POST['select_btn']=="Edit"){
    $cid = $_POST['contact_id'];
    echo $cid;
    make_one($cid);
  }
  // Handle the form submission
/*  switch($_REQUEST['btn_submit']){
    case "edit":
      echo "EDIT";
      break;
    case "dl":
      if( isset($_POST['contact_id']){
        $cid = $_POST['contact_id'];
      }else{
        echo "Contact id not in POST";
      } 
      make_one($cid);
      break;
}*/
?>
</head>
<html>
<body>
<?php 
  if( isset($_GET['id'])){
    $last_id = $_GET['id'];
  }elseif( isset($_POST['last_id'])){
    $last_id = $_POST['last_id'];
  }else{
    echo "Last_id was not passed";
  }
    
  $conn = create_db_connection();
  $sql = "SELECT f_name, l_name, c_id FROM Network WHERE c_of = $last_id";
  $result = mysqli_query($conn, $sql);
  mysqli_close($conn);
?>
<header></header>
<h1>One vCard</h1>
<div id="get_one_select_div">
  <p id="select_expl">Please select the contact from the dropdown box.  
    To download the vCard, click on Download.  To edit the vCard contents
    before downloading, click on Edit.
  </p>
  <form action="one_vcard.php" method="POST" id="select_form">
    <input type="hidden" name="last_id" value="<?php echo $last_id; ?>">
    <p id="select_p">
      <select name="contact_id">
      <?php 
        while($row = mysqli_fetch_array($result)){
          $f_name = $row['f_name'];
          $l_name = $row['l_name'];
          $c_id = $row['c_id'];
          echo '<option value="'.$c_id.'">'.$f_name.' '.$l_name.'</option>';
        }
      ?>
      </select>
    </p>
    <p id="select_buttons_p">
      <input type="submit" name="select_btn" value="Edit" id="select_edit"></button>
      <input type="submit" name="select_btn" value="Download" id="select_dl"></button>
    </p>
  </form>

</body>
</html>
