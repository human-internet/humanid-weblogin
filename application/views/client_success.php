<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Green Zone</title>

	<style type="text/css">

	::selection { background-color: #E13300; color: white; }
	::-moz-selection { background-color: #E13300; color: white; }

	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
		text-align:center;
	}
	a{
		background: #023B60;
		display: inline-block;
		padding: 10px 20px 5px;
		border-radius: 10px;
	}
	</style>
</head>
<body>
	<h1>Welcome to Green Zone!</h1>
	<img src="<?php echo base_url('assets/images/client/greenzone.png');?>" alt="Green Zone">
	<br><br><br>
	<p><strong>UserID:</strong> <?php echo time()?></p>
	<p><strong>Token:</strong> <?php echo sha1(time())?></p>
</body>
</html>