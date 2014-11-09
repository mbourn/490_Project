<!DOCTYPE html>
<html>
<head>
  <?php 
    require "cgi-bin/auth.php"; 
    
    // Get Vars
    if( isset($_POST['c_id'])){
      $c_id = $_POST['c_id'];
    }else{ die('No contact ID passed by POST or GET');
    }

    if( isset($_POST['return_addr'])){
      $return_addr = $_POST['return_addr'];
    }else{ die('Return address not passed');
    }

    // Make the connection, create the query string, submit the query
    $conn = create_db_connection();
    if( isset($_POST['update_c'])){
      $f_name = $_POST['f_name'];
      $l_name = $_POST['l_name'];
      $email = $_POST['email'];
      $phone1 = $_POST['phone1'];
      $phone2 = $_POST['phone2'];
      $addr = $_POST['addr'];
      $twtr = $_POST['twtr'];
      $i_m = $_POST['i_m'];
      $pic_path = $_POST['pic_path'];
      $sql_update = "UPDATE Network SET f_name='$f_name', l_name='$l_name', email='$email', 
                                        phone1='$phone1', phone2='$phone2', addr='$addr', 
                                        twtr='$twtr', i_m='$i_m', pic_path='$pic_path' 
                                        WHERE c_id = $c_id";
      $result = mysqli_query($conn, $sql_update);
      if( $conn->error){
        die($conn->error);
      }
      header("Location: $return_addr"); 
    }

    $sql = "SELECT * FROM Network WHERE c_id = $c_id";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_array($result);

    if( $data['f_name']){
      $f_name = $data['f_name'];
    }
    if( $data['l_name']){
      $l_name = $data['l_name'];
    }
    if( $data['email']){
      $email = $data['email'];
    }else{
      $email = NULL;
    }
    if( $data['phone1']){
      $phone1 = $data['phone1'];
    }else{
      $phone1 = NULL;
    }
    if( $data['phone2']){
      $phone2 = $data['phone2'];
    }else{
      $phone2 = NULL;
    }
    if( $data['addr']){
      $addr = $data['addr'];
    }else{
      $addr = NULL;
    }
    if( $data['twtr']){
      $twtr = $data['twtr'];
    }else{
      $twtr = NULL;
    }
    if( $data['i_m']){
      $i_m = $data['i_m'];
    }else{
      $i_m = NULL;
    }
    if( $data['pic_path']){
      $pic_path = $data['pic_path'];
    }else{
      $pic_path = NULL;
    }
    if( $data['l_url']){
      $l_url = $data['l_url'];
    }else{
      $l_url = NULL;
    }
      
 ?>
  
</head>
<body>
<header>
  <?php render_header();?>
</header>
<main>
  <span id="edit_title">
    <h4>Edit or Create a vCard</h4>
  </span>
  <span id="edit_form_span">
  <form action="edit.php" method="POST" id="edit_form"><br>
      <table id="edit_table">
      <tr>
        <td>First name:   </td>
        <td> <input type="hidden" name="f_name" value="<?php echo $f_name;?>">
            <input type="text" value="<?php echo $f_name ;?>" name="f_name"></td>
      </tr><tr>
        <td>Last name:    </td>
        <td> <input type="hidden" name="l_name" value="<?php echo $l_name;?>">
             <input type="text" value="<?php echo $l_name ;?>" name="l_name"></td>
      </tr><tr>
        <td>Email address:</td>
        <td> <input type="hidden" name="email" value="<?php echo $email;?>">
             <input type="text" value="<?php echo $email ;?>" name="email"></td>
      </tr><tr>
        <td>Phone number: </td>
        <td> <input type="hidden" name="phone1" value="<?php echo $phone1;?>">
             <input type="text" value="<?php echo $phone1 ;?>" name="phone1"></td>
      </tr><tr>
        <td>Phone number: </td>
        <td> <input type="hidden" name="phone2" value="<?php echo $phone2;?>">            
             <input type="text" value="<?php echo $phone2 ;?>" name="phone2"></td>
      </tr><tr>
        <td>Address:      </td>
        <td> <input type="hidden" name="addr" value="<?php echo $addr;?>">
             <input type="text" value="<?php echo $addr ;?>" name="addr"></td>
      </tr><tr>
        <td>Twitter:      </td>
        <td> <input type="hidden" name="twtr" value="<?php echo $twtr;?>">
             <input type="text" value="<?php echo $twtr ;?>" name="twtr"></td>
      </tr><tr>
        <td>IM:           </td>
        <td> <input type="hidden" name="i_m" value="<?php echo $i_m;?>">
             <input type="text" value="<?php echo $i_m ;?>" name="i_m"></td>
      </tr><tr>
        <td>Picture URL:  </td>
        <td> <input type="hidden" name="pic_path" value="<?php echo $pic_path;?>">
             <input type="text" value="<?php echo $pic_path ;?>" name="pic_path"></td>
      </tr><tr>
        <td><input type="hidden" name="return_addr" value="<?php echo $return_addr; ?>"></td>
        <td><input type="hidden" name="c_id" value="<?php echo $c_id; ?>"></td>
        <td><input type="submit" name="update_c" value="Update"></td>
      </tr>
      </table>
    </form>
  </span>



</main>
</body>
</html>
