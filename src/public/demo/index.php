<?php

use GuzzleHttp\Client;

require __DIR__ . '/../../vendor/autoload.php';
$env_path = $_SERVER['APP_DIR'] ?? __DIR__ . '/../..';
$dotenv = Dotenv\Dotenv::createImmutable($env_path);
$dotenv->load();

$demo1__clientId = $_ENV['DEMO_1_CLIENT_ID'];
$demo1__clientSecret = $_ENV['DEMO_1_CLIENT_SECRET'];
$demo1__appName = 'FilmReview (Demo)';
$demo1__appLogo = 'https://s3.human-id.org/s/apps/upQ8Fny2fXIDuLz1.png';

$demo2__clientId = $_ENV['DEMO_2_CLIENT_ID'];
$demo2__clientSecret = $_ENV['DEMO_2_CLIENT_SECRET'];
$demo2__appName = 'HelloStranger (Demo)';
$demo2__appLogo = 'https://s3.human-id.org/s/apps/L6emv1Yau9KPLLpC.png';

function getBaseUrl($isProduction) {
    if ($isProduction) {
        return 'https://api.human-id.org/v1';
    }

    return 'https://staging.api.human-id.org/v1';
}

function getLoginUrl($clientId, $clientSecret): string
{
    $isProduction = $_ENV['HUMANID_PRODUCTION'] === 'true';
    $uri = getBaseUrl($isProduction) . '/server/users/web-login';
    $client = new Client();
    try {
        $response = $client->request('POST', $uri, [
            'headers' => [
                "client-id" => $clientId,
                "client-secret" => $clientSecret,
            ]
        ]);
        $encoded = $response->getBody()->getContents();
        $result = json_decode($encoded);
        return $result->data->webLoginUrl;
    } catch (Exception $e) {
        return '';
    }
}

$demo1__loginUrl = getLoginUrl($demo1__clientId, $demo1__clientSecret);
$demo2__loginUrl = getLoginUrl($demo2__clientId, $demo2__clientSecret);

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>The humanID Demo</title>
	<link rel="icon" href="favicon.png">
	<style>
		@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@250&display=swap');
		@import url('https://fonts.googleapis.com/css2?family=Lato&display=swap');
	</style>
	<style type="text/css">
		::selection { background-color: #E13300; color: white; }
		::-moz-selection { background-color: #E13300; color: white; }
		body {
			background-color: #fff;
			font: 14px/20px normal Helvetica, Arial, sans-serif;
			color: #343434;
			text-align:center;
		}
		a{
			background: #023B60;
			display: inline-block;
			padding: 10px 20px 5px;
			border-radius: 10px;
		}
		h1{
			line-height: 2.0;
			font-size: 40px;
			width: 85%;
			margin: auto;
			margin-bottom: 1.5%;
			font-family: 'Lato';
			font-style: normal;
			font-weight: 700;

		}

		h2{
			line-height: 1.5;
			width: 78%;
			margin: auto;
			margin-bottom: 1.5%;
			font-size: 15px;
			font-family: 'Montserrat';
			font-style: normal;
			font-weight: 700;
			margin-bottom: 4%;
			color: black;


		}
		select{
			padding: 10px 10px;
			border-radius: 10px;
			border: 1px solid #4F5155;
		}

		.rectangle{
			background: #FFFFFF;
			border: 0.1px solid #000000;
			box-sizing: border-box;
			border-radius: 15px;
			margin:auto;
			position: relative;
			padding: 30px;
			width: 45%;
			box-shadow: 0 4px 2px -2px gray;
			z-index: 1;
		}

		#rectangle *{
			margin-bottom: 8%;
		}

		.logo{
			margin-bottom: 30px;
		}

        .login-button {
            display: flex;
            align-items: center;
            flex-direction: column;
            flex-wrap: wrap;
        }

		.dummy{
			text-align: center;
			background-color:#C4C4C4;
			color: black;
			display: flex;
			align-items: center;
			height: 50.5px;
			width: 379px;
			border-radius: 7px;
			justify-content: center;
			margin: auto;
			margin-top: 13px;
			margin-bottom: 48px;
			z-index: 2;

			font-family: 'Montserrat';
			font-style: normal;
			font-size: 15px;
			font-weight: 400;
			line-height: 18px;
			letter-spacing: 0em;
		}

		.or{
			margin: auto;
			width: 379px;
			height: 45.5px;
			display:flex;
			align-items: center;
			justify-content: center;
			flex-direction: row;
		}
		.line1, .line2{
			display:flex;
			width: 155px;
			height: 0px;
			background-color: black;
			border: 0.8px solid #000000;
		}

		.line1{
			margin-right: 20px;
		}

		.line2{
			margin-left: 20px;
		}

		.message{
            padding:20px;
			margin: auto;
			position: absolute;
			height: 54px;
			width: 270px;
			color: white;
			background-color: #023B60;
			border-radius: 7px;
			align-items: center;
			justify-content: center;
			text-align: center;
			opacity: 0;
			font-family: 'Montserrat';
			font-style: normal;
			font-weight: 400;
			font-size: 11px;
			line-height: 15px;
			z-index: 999;

			position: absolute;
			left: 26%;
			top: 90%;
			transition: opacity 0.5s ease;
		}

		.message:before{
			content: "";
			position: absolute;
			left: 50%;
			bottom:100%;
			border-left: 8px solid transparent;
  			border-right: 8px solid transparent;

  			border-bottom: 8px solid #023B60;
		}

		.dummy:hover ~ .message{
			opacity: 1;
			position: absolute;
		}

		.humanid-button{
			height: 30px;
    		aspect-ratio: auto 339 / 30;
    		width: 339px;
		}


		@media only screen and (max-width:936px){
			.rectangle{
				border: white;
				box-shadow: 0 0 0 0;
			}
			.dummy{
				width:250px;
			}

			.or{
				right: 20px;
				position: relative;

			}
			.line1, .line2{
				width: 90px;
			}
			.humanid-button{
				width: 210px;
			}
		}

		@media only screen and (max-width:720px)  {
			.or{
				right:70px;
			}

			.logo{
				left: 50px;
				position: relative;
			}

			.rectangle{
				right: 50px;
			}
}
	</style>
</head>
<body>
	<h1>Try the fastest & safest signup & login flow.</h1>
    <h2>Keep bots out - but never lose a real user to privacy concerns again! <br>
        humanID's anonymous authentication can be integrated standalone, or alongside Single SignOns such as <br>
        Google Login, or traditional Email & Password Logins.
    </h2>
	<div class="rectangle">
		<div class="logo">
            <img src="logo.png" alt="Demo App" height="120">
        </div>
        <div class="login-button">
<!--            <p>Try the default humanID flow:</p>-->
            <a href="<?php echo $demo2__loginUrl ?>">
                <img class="humanid-button" src="anonymous-login.svg" alt="Anonymous Login with humanID">
            </a>
        </div>
        <div class="or">
            <div class="line1"></div>
            <div class="ortext">or</div>
            <div class="line2"></div>
        </div>
	<!--	<div class="login-button">
            <p>Try humanID with account recovery mode <br> optional for both clients and users:</p>
            <a href="<?php /*echo $demo1__loginUrl */?>">
                <img class="humanid-button" src="anonymous-login.svg" alt="Anonymous Login with humanID">
            </a>
        </div>
		<div class="or">
			<div class="line1"></div>
			<div class="ortext">or</div>
			<div class="line2"></div>
		</div>-->
		<div class="dummy">Other forms of login</div>
		<div class="message">
            humanID is an alternative to Single-Sign-Ons like "Login with Facebook".
            For this demonstration, please try humanID above
        </div>
	</div>
</body>
</html>
