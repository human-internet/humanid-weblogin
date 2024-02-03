<form method="post">
    <script>
        document.addEventListener('DOMContentLoaded', () => {
        let countdown = 60; // Set the countdown starting time in seconds
        const countdownTime = document.getElementById('countdown-time'); // The <strong> element inside the span
        const resendLink = document.getElementById('resend-link'); // The resend link

        // Update the countdown every second
        const timerInterval = setInterval(() => {
            countdown -= 1; // Decrement the countdown

            // Check if the countdown has finished
            if (countdown < 0) {
                clearInterval(timerInterval); // Stop the countdown
                resendLink.style.display = 'inline'; // Show the resend link
            }
        }, 1000); // Repeat every 1000 milliseconds (1 second)
        });
    </script>

    <div class="humanid-logo">
        <div class="humanid-logo__placement">
        <img src="<?php echo $app->logoUrls->thumbnail;?>" alt="<?php echo $app->name; ?>">
        </div>
    </div>
    <?php if($success):?>
        <div class="humanid-page-title"><?php echo str_replace("{APPNAME}",$app->name,$lang->text->pageTitleApp);?></div>
        <div class="humanid-content-text">
            <div class="humanid-text-info humanid-text-info-default">
                <p><?php echo $lang->text->welcome;?></p>
                <p><?php echo str_replace("{APPNAME}",$app->name,$lang->text->continue);?></p>
            </div>
        </div>
        <div class="humanid-button humanid-button-vertical">
            <button class="btn-humanid btn-humanid-primary directed-now" type="button"><?php echo $lang->redirect;?></button>
            <button class="btn-humanid btn-humanid-secondary" type="button"><?php echo str_replace(array("{TIMER}","{APPNAME}"),array('<span class="timer-text"></span>',$app->name),$lang->text->timer);?></button>
            <input type="hidden" class="directed-link" value="<?php echo $redirectUrl;?>">
        </div>
        <?php /*if(!$hasSetupRecovery && $accountRecovery === true) { */?><!--
                <div class="humanid-content-link center">
                    <a href="<?php /*echo $redirectSetRecoveryEmail;*/?>" class="humanid-link-red">Recover an existing account instead</a>
                </div>
        --><?php /*} */?>
    <?php else:?>
        <div class="humanid-page-title"><?php echo $lang->verify;?></div>
        <div class="humanid-content-text">
            <div class="humanid-text-info humanid-text-info-default">
                <p><?php echo str_replace(array("{COUNTRYCODE}","{PHONE}"),array($row['dialcode'], $display_phone),$lang->text->verifyCode);?></p>
                <p><?php echo $lang->text->afterSuccessful;?></p>
            </div>
            <div class="humanid-text-info humanid-text-info-danger">
                <?php if(isset($error_message) && !empty($error_message)):?><p><?php echo $error_message;?></p><?php endif;?>
            </div>
        </div>
        <div class="humanid-form-placement">
            <div class="humanid-form-placement__otp-verification">
                <?php if (isset($_GET['s']) && $_GET['s'] == 'm') { ?>
                    <div class="humanid-form-group" style="flex: 3">
                        <input type="tel" oninput="humanid.handleInputOtp(this, true)"
                               maxlength="4" pattern="[0-9]*" class="humanid-input-otp" name="code"
                               id="single-factor-code-text-field" autocomplete="one-time-code">
                    </div>
                    <button class="btn-humanid btn-humanid-primary btn-submit-otp" disabled type="submit" style="flex: 1">Submit</button>
                <?php } else { ?>
                    <div class="humanid-form-group">
                        <input type="tel" oninput="humanid.handleInputOtp(this, false)"
                               onpaste="humanid.handlePasteOtp(this, event)"
                               maxlength="4" pattern="[0-9]*" class="humanid-input-otp" name="code"
                               id="single-factor-code-text-field" autocomplete="one-time-code">
                    </div>
                <?php } ?>
            </div>
          <span class="timer-text verify-area timer"><?php echo str_replace("{TIME}",'<strong id="countdown-time">00:60</strong>',$lang->text->resend);?></span>
            <input type="hidden" name="remaining" id="remaining">
            <div class="humanid-form-placement__link">
                <div class="humanid-form-placement__link__wrapper">
                    <a href="<?php echo site_url('login?a='.$app->id.'&t='.$row['token'].'&lang='.$lang->id.'&priority_country='.$pc->code);?>" class="humanid-link-blue-light">
                        <?php echo $lang->try;?>
                    </a>
                    <a href="<?php echo site_url('login/resend?a='.$app->id.'&t='.$row['token'].'&lang='.$lang->id);?>" id="resend-link" class="humanid-link-blue-light" style="display: none;"><?php echo $lang->resend;?></a>
                    <!--<a href="<?php /*echo base_url('recovery/new_number') */?>" class="humanid-link-blue-light">Recover Existing Account</a>-->
                </div>
            </div>
        </div>
    <?php endif;?>
</form>