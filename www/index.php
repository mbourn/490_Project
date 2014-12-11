<!DOCTYPE html>
<html>
<head>
<?php require "cgi-bin/auth.php" ?>
<link rel="stylesheet" href="main.css">
</head>
<body>
<header>
  <?php render_header(); ?>
</header>
<div id="main">
<div id="index_main_div" class="maindiv">
  <div id="login_btn_div">
    <button id="login_btn" onclick="window.location=href='https://mbourn.com/authorized/authorized.php'">
      Get Started
    </button>
  </div>
  <div id="header_expl" style="text-align: center;">
    <b>Welcome to the LinkedIn Contact Info Grabber</b>. If you have a Linked in account then 
    this web page will be able to get the contact information for the people in your network, 
    and turn it into vCards.  These vCards can then be imported into your iPhone, Android 
    phone, Google Contacts, Outlook, etc. 
  </div>
</div>
</div>
<footer>
<?php render_footer(); ?>
</body>
</html>
