<!DOCTYPE html>
<html>
<head>
<?php 
  include "cgi-bin/auth.php"; 
  error_reporting(E_ALL);
  ini_set('display_errors','On');

/*  <script type="text/javascript">



  Ext.onReady(function(){
        Ext.Msg.prompt('Hey!', 'Tell me something', function(btn, text){
                if (btn == 'ok'){
                          var data = text;
                                }
                    }, this, true, 'hi');
  });

  </script>

 */
?>

<style> 
#pform { 
position:absolute; 
top:100px; 
left:100px; 
display:none; 
border:2px solid blue; 
padding:8px; 
} 
</style> 
<script type="text/javascript"> 
function prompt() { 
// get field to be validated 
var pf = document.getElementById( 'pForm' ); 
pf.style.display = 'block'; 
} 

function getPdata( arg ) { 
var f = document.getElementById( 'pForm' ); 
if ( 'cancel' == arg ) { 
f.style.display = 'none';	// hide form 
return;	// exit immediately 
} 
else if ( 'default' == arg) { 
// don't know what "default" means 
f.style.display = 'none';	// hide form 
return;	// exit immediately 
} 
else { 
var n = f.name.value; 
var a = parseInt( f.age.value ); 
f.style.display = 'none';	// hide form 
alert( 'name: ' + n + '\n age: ' + a ); 
} 
} 
</script> 
</head> 
<body> 
<form> 
<input type="button" value="prompt" onclick="prompt()" /> 
</form> 
<div id="p"> 
<form id="pForm"> 
name: <input type="text" name="name" /><br><br> 
age: <input type="text" name="age" /><br><br> 
<input type="button" value="OK" onclick="getPdata()" /> 
<input type="button" value="default" onclick="getPdata( this.value )" /> 
<input type="button" value="cancel" onclick="getPdata( this.value )" /> 
</form>
</body>
</html>
