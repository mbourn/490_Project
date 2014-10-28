<!DOCTYPE html>
<html>
<head>
<script>
  function getToken(){
    var httpReq=new XMLhttpRequest();
    httpReq.onreadystatechange=function(){
      if (httpReq.readyState==4 && httpReq.status==200){
        var code=$_GET['code'];
        httpReq.open("GET", "href=\"https://www.linkedin.com/uas/oauth2/accessToken?grant_type=authorization_code&code=$code&redirect_uri=https://mbourn.com/authorized/authorized.php&client_id=75p5ptqc5060jj&client_secret=GJIsoTietQCigBWb", true);
        httpReq.send();
        document.getElementById("tokenResponse").innerHTML=httpReq.responseText;
      }
    }
  }
</script>
</head>
<body>
<?php
if ($_GET['state'] != '33555d7e98ed0cc08105ef69345daeae') {
  echo "<h1>Silly h@x0r</h1>";

}elseif ($_GET['error_description'] == 'the user denied your request') {
  echo "<h1>It hurts me that you don't trust my app.  I think I shall go have a cry.</h1>";

}else{
  $code=$_GET['code'];
  echo " <h1>You've been authorized, bru!</h1> <br><br>";
  echo " <div id='tokenResponse'>The JSON token goes here<div/><br><br>";
  if ($tokenResponse){
    echo "got the token";
  }else{
    echo "it didn't work";
  }

  echo "<h3>Code=</h3> " . $_GET['code'] . "<br><h3>Hash=</h3> " . $_GET['state'];
  echo "<a href=\"https://www.linkedin.com/uas/oauth2/accessToken?grant_type=authorization_code&code=$code&redirect_uri=https://mbourn.com/authorized/authorized.php&client_id=75p5ptqc5060jj&client_secret=GJIsoTietQCigBWb\"><br><br>Get you a token!</a>";
}?>

</body>
</html>

