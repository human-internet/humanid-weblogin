<?php header('X-Frame-Options: DENY');?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Anonymous Login with humanID</title>
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css');?>">
    <link rel="icon" href="<?php echo base_url('assets/images/favicon.png');?>">
    <style>
        .humanid-input-default{
            font-size:1rem;
        }
        .humanid-form-placement__default-main {
            max-width: 13.5rem;
        }
        .humanid-form-placement__otp-resend{
            margin-top:1rem;
            color:#023B60;
        }
        .humanid-form-placement__otp-resend .timer{
            margin-right:10rem;
        }
        .humanid-form-placement__otp-resend a{
            font-weight: 600;
        }
        @media only screen and (max-width: 768px) {
            .humanid-form-placement__otp-resend .timer{
                margin-right:2rem;
            }
            .humanid-form-placement__otp-resend a{
                font-size: 0.75rem;
            }
        }
    </style>
    <?php echo $styles; ?>
</head>
<body>
    <div class="humanid-header">
        <img src="<?php echo base_url('assets/images/anonymous-login.svg');?>" alt="Anonymous Login with humanID">
    </div>
    <div class="humanid-container">
        <?php echo $view;?>
    </div>

    <script src="<?php echo base_url('assets/js/jquery.min.js');?>"></script>
    <script src="<?php echo base_url('assets/js/intlTelInput.min.js');?>"></script>
    <script src="<?php echo base_url('assets/js/humanID.js');?>"></script>
    <?php echo $scripts; ?>
</body>
</html>