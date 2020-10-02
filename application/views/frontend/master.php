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
            max-width: 15.5rem;
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
        .humanid-modal {
            display: block; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            padding-top: 100px; /* Location of the box */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }
        .humanid-modal .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            text-align:center;
        }
        .humanid-modal .modal-content h3 {
            margin: 0 0 10px;
        }
        .humanid-modal .modal-content p {
            margin: 0 0 30px;
        }
        .humanid-modal .modal-content span {
            color: #ffffff;
            font-size: 18px;
            font-weight: bold;
            background-color: #aaaaaa;
            padding: 5px 15px;
            border-radius: 10px;
            cursor: pointer;
        }
        @media only screen and (max-width: 768px) {
            .humanid-form-placement__otp-resend .timer{
                margin-right:2rem;
            }
            .humanid-form-placement__otp-resend a{
                font-size: 0.75rem;
            }
            .humanid-modal .modal-content {
                width: 80%;
            }
        }
    </style>
    <?php echo $styles; ?>
    <style id="antiClickjack">body{display:none !important;}</style>
    <script type="text/javascript">
        if (self === top) { var antiClickjack = document.getElementById("antiClickjack");antiClickjack.parentNode.removeChild(antiClickjack);} else {top.location = self.location;}
    </script>
</head>
<body>
    <div class="humanid-header">
        <img src="<?php echo base_url('assets/images/anonymous-login.svg');?>" alt="Anonymous Login with humanID">
    </div>
    <div class="humanid-container">
        <?php echo $view;?>
    </div>
    <?php if(isset($modal)):?>
        <div id="humanidModal" class="humanid-modal">
            <div class="modal-content">
                <h3><?php echo $modal->title;?></h3>
                <p><?php echo $modal->message;?></p>
                <input type="hidden" id="redirectUrlFail" value="<?php echo $modal->url;?>">
                <span class="close"><?php echo $lang->ok;?></span>
            </div>
        </div>
    <?php endif;?>
    <script src="<?php echo base_url('assets/js/jquery.min.js');?>"></script>
    <script src="<?php echo base_url('assets/js/intlTelInput.min.js');?>"></script>
    <script src="<?php echo base_url('assets/js/humanID.js');?>"></script>
    <?php echo $scripts; ?>
    <?php if(isset($modal)):?>
        <script>
            $(function(){
                $('.humanid-modal .close').click(function(){
                    window.location = $('#redirectUrlFail').val();
                });
            });
        </script>
    <?php endif;?>
</body>
</html>