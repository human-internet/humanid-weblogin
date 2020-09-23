<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>The humanID Demo</title>
	<link rel="icon" href="favicon.png">
	<style type="text/css">
		::selection { background-color: #E13300; color: white; }
		::-moz-selection { background-color: #E13300; color: white; }
		body {
			background-color: #fff;
			margin: 40px;
			font: 14px/20px normal Helvetica, Arial, sans-serif;
			color: #4F5155;
			text-align:center;
		}
		a{
			background: #023B60;
			display: inline-block;
			padding: 10px 20px 5px;
			border-radius: 10px;
		}
		h1{
			line-height: normal;
		}
		select{
			padding: 10px 10px;
			border-radius: 10px;
			border: 1px solid #4F5155;
		}
	</style>
</head>
<body>
	<h1>Welcome to the humanID demo</h1>
	<img src="greenzone.png" alt="Demo App" height="90">
	<br><br><br>
	<p>See how fast & convenient anonymous authorization and taking back control of your data can be</p>
	<p> Change Language: 
		<select onchange="changeLang()" id="changeLang">
			<option value="ar_SA">Arabic</option>
			<option value="bn_IN">Bengali</option>
			<option value="en_rGB">British English</option>
			<option value="zh_TW">Chinese, Taiwan</option>
			<option value="zh_CN">Chinese, China</option>
			<option value="hr_HR">Croatian</option>
			<option value="en_US" selected>English US</option>
			<option value="fr_FR">French</option>
			<option value="de_DE">German</option>
			<option value="el_GR">Greek</option>
			<option value="hi_IN">Hindi</option>
			<option value="in_ID">Indonesian</option>
			<option value="it_IT">Italian</option>
			<option value="ja_JP">Japanese</option>
			<option value="ko_KR">Korean</option>
			<option value="ms_MY">Malay</option>
			<option value="pl_PL">Polish</option>
			<option value="pt_PT">Portuguese</option>
			<option value="ru_RO">Russian</option>
			<option value="ro_RO">Romanian, Romania</option>
			<option value="sv_SE">Swedish, Sweden</option>
			<option value="es_ES">Spanish</option>
			<option value="tr_TR">Turkish</option>
			<option value="tl_PH">Tagalog</option>
			<option value="th_TH">Thai</option>
			<option value="vi_VN">Vietnamese</option>
		</select>
	</p>
	<a href="login.php?lang=en_US" id="login"><img src="anonymous-login.svg" alt="Anonymous Login with humanID" height="27"></a>
	
	<script>
		function changeLang() {
			var x = document.getElementById("changeLang");
			document.getElementById("login").setAttribute("href", "login.php?lang=" + x.value);
		}
	</script>
</body>
</html>