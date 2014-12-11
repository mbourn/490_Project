
<!DOCTYPE html>
<html>
<head>
<?php 
  require "cgi-bin/auth.php";
  //error_reporting(E_ALL);
  //ini_set('display_errors','On');
  $last_id=$_GET['last_id'];
  $errors=$_GET['errors'];
?>
  <link rel="stylesheet" href="main.css">
</head>
<body>
<header>
  <?php 
    render_header(); 
  ?>
</header>
<div id="main">
  <div id="priv_er_cont">
  <div id="privates_div">
    <?php $count=count_privates($last_id); ?>
    <div id="priv_title" class="title">Private Accounts</div>
    <div id="priv_res"><?php echo $count; ?></div>
    <div id="priv_expl">The number of your contacts that have set their profile to private.</div>
  </div>

  <div id="errors_div">
    <div id="er_title" class="title">Errors</div>
    <div id="er_res"><?php echo $errors; ?></div>
    <div id="er_expl">The number of errors encountered while getting your contacts.</div>
  </div>
  </div>

  <div id="get_cont">
  <div id="get_one_div" class="opt_cont">
    <div id="get_one_expl">
      <div id="get_one_title" class="title2">Create One<br> vCard</div>
      If you would like to create a vCard a specific contact or create
      a new vCard from scratch, click Continue:
    <button onclick="window.location=href='https://mbourn.com/one_vcard.php?id=<?php echo $last_id; ?>'">Continue
      </button>
    </div>
  </div>

  <div id="get_multi_div" class="opt_cont">
    <div id="get_multi_expl">
      <div id="get_multi_expl" class="title2">Create Multiple Vcards</div>
      If you would like to create multiple vCards, but not the whole set, click Continue:<br>
        <button onclick="window.location=href='https://mbourn.com/multi_vcard.php?id=<?php echo $last_id; ?>'">Continue</button>
    </div>
  </div>

  <div id="get_all_div" class="opt_cont"> 
    <div id="get_all_expl">
      <div id="get_all_title" class="title2">Create The Whole Set of vCards</div>
      If you would like to create and download the entire set of vCards, click Continue:<br>
      <?php echo '<button onclick="window.location=href=\'https://mbourn.com/multi_vcard.php?id='.$last_id.'&action=all\'">Continue</button>'; ?>
    </div>
  </div>
  </div>
</div>
<footer>
<?php render_footer(); ?>
</footer>
</body>
</html>
