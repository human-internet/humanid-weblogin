<form action="<?php echo base_url('recovery-exist/confirmation-login') ?>" method="post">
    <div class="humanid-logo">
        <div class="humanid-logo__placement">
            <img src="<?php echo base_url('assets/images/humanid-logo.svg'); ?>" alt="humanID"
                 style="">
        </div>
    </div>

    <div class="humanid-page-title">
        Recover Account
    </div>

    <div class="humanid-content-text humanid-fw-normal">
        <p> Please enter the 4 digit code you received as SMS to +1 617 999 9999 </p>
    </div>

    <div class="humanid-content-text humanid-fz-18">
        <p>After successful verification, your number will be deleted <br>
            permanently. Only a random identifier will be stored.
        </p>
    </div>

    <div class="humanid-form-placement">
        <div class="humanid-form-placement__otp-verification">
            <div class="humanid-form-group">
                <input type="number" class="humanid-input-otp" data-id="1" maxlength="1" name="code_1" autofocus>
            </div>
            <div class="humanid-form-group">
                <input type="number" class="humanid-input-otp" data-id="2" maxlength="1" name="code_2">
            </div>
            <div class="humanid-form-group">
                <input type="number" class="humanid-input-otp" data-id="3" maxlength="1" name="code_3">
            </div>
            <div class="humanid-form-group">
                <input type="number" class="humanid-input-otp" data-id="4" maxlength="1" name="code_4">
            </div>
        </div>
        <span class="timer-text verify-area timer"
              style="display: none;"><?php echo str_replace("{TIME}", '<strong>00:60</strong>', $lang->text->resend); ?></span>
        <input type="hidden" name="remaining" id="remaining">
    </div>
    <div class="humanid-form-placement__otp-resend humanid-content-link">
        <span class="timer-text verify-area timer humanid-text-blue-dark"><?php echo str_replace("{TIME}",'<strong>00:60</strong>',$lang->text->resend);?></span>
        <input type="hidden" name="remaining" id="remaining">
        <a href="javascript:void(0)" class="resend-area timer humanid-text-blue-dark" style="display:none;"><?php echo $lang->resend;?></a>
        <a href="javascript:void(0)" class="humanid-text-blue-dark"><?php echo $lang->try;?></a>
    </div>
</form>
