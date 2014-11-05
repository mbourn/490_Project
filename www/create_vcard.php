<!DOCTYPE html>
<html>
<head></head>
<body>
<h1>Create the vCards</h1>
<p><b>last id = <?php echo intval($_GET['id']) ?></b></p>
<?php
  // Create the database connection
  $servername = "localhost";
  $username = "from_web";
  $password = 'Z!s2D#r4%';
  $dbname = "490_db";

  $conn = new mysqli($servername, $username, $password, $dbname);
  if( $conn->connect_error ){
    die("Connection failed: " . $conn->connect_error);
  }else{
    echo "Connected successfully ...<br>";
  }

  $sql="SELECT f_name, l_name, l_url FROM Network WHERE c_of =".strval($_GET['id']);
echo $sql;
  $result = mysqli_query($conn, $sql);

//var_dump($return);

  /*
  while($row = mysqli_fetch_array($result)){

    $id=$row['c_id'];
    $of=$row['c_of'];
    $f_name=$row['f_name'];
    $l_name=$row['l_name'];
    $l_url=$row['l_url'];
    $email=$row['email'];
    $addr=$row['addr'];
    $phone1=$row['phone1'];
    $phone2=$row['phone2'];
    $twitr=$row['twitr'];

    $vcard_content = "BEGIN:VCARD\r";
    $vcard_content .= "VERSION:3.0\r";
    $vcard_content .= "N:".$l_name.";".$f_name.";;\r"; 
    $vcard_content .= "item2.URL;tpe=pref:".$l_url."\r";
    $vcard_content .= 'item2.X-ABLabel:_$!<LinkedInPage>!$_\r';
    $vcard_content .= "X-ABShowAs:COMPANY\r";
    $vcard_content .= "END:VCARD";

    echo "<pre><p><b>".$vcard_content."</b></p></pre>";
  }*/

  error_reporting(E_ALL);
  ini_set('display_errors','On');
  $row = mysqli_fetch_array($result);
  var_dump($row);
  $f_name=$row['f_name'];
  $l_name=$row['l_name'];
  $l_url=$row['l_url'];

  $vcard_content = "BEGIN:VCARD\r";
  $vcard_content .= "VERSION:3.0\r";
  $vcard_content .= "N:".$l_name.";".$f_name.";;\r"; 
  $vcard_content .= "item2.URL;tpe=pref:".$l_url."\r";
  $vcard_content .= 'item2.X-ABLabel:_$!<LinkedInPage>!$_\r';
  $vcard_content .= "X-ABShowAs:COMPANY\r";
  $vcard_content .= "END:VCARD";
  echo "<pre><p><b>".$vcard_content."</b></p></pre>";

  $vcard = fopen($_SERVER['DOCUMENT_ROOT']."/public/vcard.vcf", "w") or die("Doh!");
  fwrite($vcard, $vcard_content);
  fclose($vcard);
  $vcard = $_SERVER['DOCUMENT_ROOT'].'/public/vcard.vcf';

  // Forces download of file
  if (file_exists($vcard)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($vcard));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($vcard));
    ob_clean();
    flush();
    readfile($vcard);
    exit;
  }
?>

</body>
</html>
