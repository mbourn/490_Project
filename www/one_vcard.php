<!DOCTYPE html>
<head>
<?php 
  require "cgi-bin/auth.php"; 
  error_reporting(E_ALL);
  ini_set('display_errors','On');

  var_dump($_POST);

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
  }else{
    echo "ERROR: POST was malformed.<br>";
  }

  // Take appropriate action  
  switch($post_action){
    // If the user clicked on edit, perform edit functions
    case "Edit":
      echo "EDIT";
    break;

    // If the user clicked on Download, perform download functions
    case "Download":
      if( isset($_POST['contact_id'])){
        $cid = $_POST['contact_id'];
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
      //render_search_result($search_result);
      if($search_result->num_rows > 0){
        echo "<br>Found<br>";
      }else{
        echo "<br>None<br>";
      }

    break;
  }
?>
</head>
<html>
<body>
<?php 
  $conn = create_db_connection();
  $sql = "SELECT f_name, l_name, c_id FROM Network WHERE c_of = $last_id ORDER BY 2 ASC";
  $result = mysqli_query($conn, $sql);
  mysqli_close($conn);
?>
<header></header>
<h1>One vCard</h1>
<div id="get_one_select_div">
  <p id="select_expl_p">Please select the contact from the dropdown box.  
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
      <input type="submit" name="select_btn" value="Edit" id="select_edit">
      <input type="submit" name="select_btn" value="Download" id="select_dl">
    </p>
  </form>
</div>

<?php render_search_div(isset($_POST['search_btn']), $search_result, $last_id); ?>

</body>
</html>