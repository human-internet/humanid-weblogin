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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        .material-symbols-outlined {
            color: white;
            font-variation-settings:
                'FILL' 0,
                'wght' 400,
                'GRAD' 0,
                'opsz' 24
        }

        :root {
            --headerHeight: 0;
        }

        body {
            background-image: none;
        }
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
        .humanid-modal__overlay {
            position: fixed;
        }

        .humanid-modal__modal {
            position: fixed;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            top: 0;
            padding: 16px;
        }

        .humanid-modal__modal.active {
            display: flex;
            align-items: center;
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

        .humanid-background {
            position: fixed;
            right: -190.34px;
        }

        .humanid-header {
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .humanid-header select {
            display: block;
            position: relative;
            top: auto;
            right: auto;
            float: right;
        }

        .humanid-lang-logo {
            display: none;
            text-align: right;
        }

        .humanid-lang-logo img {
            width: 22px;
            height: 20px;
        }

        .humanid-select {
            display: flex;
            justify-content: end;
            align-items: center;
            flex: 1;
        }

        .humanid-lang-container {
            position: fixed;
            right: 0;
            top: 0;
            display: none;
            z-index: 9;
            width: 100vh;
            height: calc(100vh - var(--headerHeight, 40px));
        }

        .humanid-lang-options {
            position: absolute;
            right: 0;
            top: var(--headerHeight, 40px);
            background-color: lightgray;
            list-style-type: none;
            padding: 0 4px;
            margin: 0;
            overflow: auto;
            width: fit-content;
            height: fit-content;
            max-height: calc(100vh - var(--headerHeight, 40px));
        }

        .humanid-lang-options li {
            text-align: left;
            text-decoration: none;
            padding: 4px 16px;
            position: relative;
            cursor: pointer;
        }

        .humanid-lang-options li:hover {
            background-color: gray;
            color: white;
            border-radius: 8px;
        }

        .humanid-lang-options li::before {
            content: "\2713";
            display: none;
            position:absolute;
            top: 0;
            left: 0;
            padding: 4px;
        }

        .humanid-lang-options .show-selected::before {
            display: block;
        }

        .humanid-lang-options .show-selected {
            background-color: gray;
            color: white;
            border-radius: 8px;
        }

        .humanid-header-image {
            flex: 1;
        }

        .humanid-back-button {
            color: white;
            display: flex;
            align-items: center;
            flex: 1;
        }

        @media only screen and (max-width: 768px) {
            .humanid-form-placement__otp-resend .timer {
                margin-right: 2rem;
            }

            .humanid-form-placement__otp-resend a {
                font-size: 0.75rem;
            }

            .humanid-modal .modal-content {
                width: 80%;
            }

            .humanid-header {
                padding: 0.5rem;
            }

            .humanid-header::before {
                width: 0px;
            }

            .humanid-header select {
                display: none;
            }

            .humanid-lang-logo {
                display: flex;
            }

            .humanid-header img {
                max-width: 264px;
                min-width: 0;
            }

            .humanid-header-image {
                padding: 0 1rem;
            }

            .humanid-select {
                flex: 0;
            }

            .humanid-back-button {
                flex: 0;
            }

            .humanid-container {
                padding-top: var(--headerHeight, 40px);

            }

            .humanid-logo {
                padding: 1.5rem;
            }

            .humanid-logo__placement {
                margin: 0;
            }

            .humanid-logo__placement img {
                margin: 0;
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
    <img src="<?php echo base_url('assets/images/bg-finger-print.svg');?>" alt="bg" class="humanid-background">
    <div id="humanidHeader" class="humanid-header">
        <div class="humanid-back-button">
            <?php if (isset($_GET['s']) && $_GET['s'] == 'm') { ?>
                <a href="<?= base_url('/back') ?>" style="display: flex;">
                    <span class="material-symbols-outlined">arrow_back_ios_new</span>
                </a>
            <?php } else { ?>
                <span class="material-symbols-outlined" style="opacity: 0;">arrow_back_ios_new</span>
            <?php } ?>
        </div>
        <img src="<?php echo base_url('assets/images/anonymous-login.svg');?>" alt="Anonymous Login with humanID" class="humanid-header-image">
        <div class="humanid-select">
            <select id="changeLang">
                <option value="ar_SA">Arabic</option>
                <option value="bn_IN">Bengali</option>
                <option value="en_rGB">British English</option>
                <option value="zh_TW">Chinese, Taiwan</option>
                <option value="zh_CN">Chinese, China</option>
                <option value="hr_HR">Croatian</option>
                <option value="en_US" selected>English US</option>
                <option value="fr_FR">French</option>
                <option value="el_GR">Greek</option>
                <option value="hi_IN">Hindi</option>
                <option value="in_ID">Indonesian</option>
                <option value="it_IT">Italian</option>
                <option value="ja_JP">Japanese</option>
                <option value="ko_KR">Korean</option>
                <option value="ms_MY">Malay</option>
                <option value="pl_PL">Polish</option>
                <option value="pt_PT">Portuguese</option>
                <option value="ru_RU">Russian</option>
                <option value="ro_RO">Romanian, Romania</option>
                <option value="sv_SE">Swedish, Sweden</option>
                <option value="es_ES">Spanish</option>
                <option value="tr_TR">Turkish</option>
                <option value="tl_PH">Tagalog</option>
                <option value="th_TH">Thai</option>
                <option value="vi_VN">Vietnamese</option>
            </select>

            <div class="humanid-lang-logo" id="selectLang">
                <img src="<?php echo base_url('assets/images/select-language.svg');?>" alt="Select Language Logo" id="selectLangLogo">
            </div>
        </div>
    </div>

    <div class="humanid-lang-container" id="humanidLangContainer">
        <ul class="humanid-lang-options" id="humanidLangOptions">
            <li data-lang="ar_SA">Arabic</li>
            <li data-lang="bn_IN">Bengali</li>
            <li data-lang="en_rGB">British English</li>
            <li data-lang="zh_TW">Chinese, Taiwan</li>
            <li data-lang="zh_CN">Chinese, China</li>
            <li data-lang="hr_HR">Croatian</li>
            <li data-lang="en_US">English US</li>
            <li data-lang="fr_FR">French</li>
            <li data-lang="de_DE">German</li>
            <li data-lang="el_GR">Greek</li>
            <li data-lang="hi_IN">Hindi</li>
            <li data-lang="in_ID">Indonesian</li>
            <li data-lang="it_IT">Italian</li>
            <li data-lang="ja_JP">Japanese</li>
            <li data-lang="ko_KR">Korean</li>
            <li data-lang="ms_MY">Malay</li>
            <li data-lang="pl_PL">Polish</li>
            <li data-lang="pt_PT">Portuguese</li>
            <li data-lang="ru_RU">Russian</li>
            <li data-lang="ro_RO">Romanian, Romania</li>
            <li data-lang="sv_SE">Swedish, Sweden</li>
            <li data-lang="es_ES">Spanish</li>
            <li data-lang="tr_TR">Turkish</li>
            <li data-lang="tl_PH">Tagalog</li>
            <li data-lang="th_TH">Thai</li>
            <li data-lang="vi_VN">Vietnamese</li>
        </ul>
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
    <script>
        $(function(){
            $("#changeLang option[value='<?php echo isset($_GET['lang'])? $_GET['lang'] : 'en_US';?>']").attr('selected','selected');
            $('#changeLang').change(function (){
                var currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('lang', $(this).val());
                let url = currentUrl.href;
                window.location.href=url;
            })

            $("#selectLangLogo").click(function (e) {
                var options = $("#humanidLangContainer");
                if (options.is(":visible")) {
                    options.hide();
                } else {
                    options.show();
                }
            })

            $("#humanidLangContainer").click(function (e) {
                var container = $("#humanidLangContainer");
                container.hide();
            }).children().click(function (e) {
                return false;
            })

            $("[data-lang]").each(function () {
                if($(this).data('lang') == "<?php echo isset($_GET['lang'])? $_GET['lang'] : 'en_US'; ?>") {
                    $(this).addClass('show-selected')
                }
            })
            $("[data-lang]").click(function (e) {
                var currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('lang', $(this).data('lang'));
                let url = currentUrl.href;
                window.location.href=url;
            })

            $(document).ready(function() {
                const h = $("#humanidHeader").height();
                document.documentElement.style.setProperty("--headerHeight", `${h + 16}px`);
            })
        });
    </script>
</body>
</html>
