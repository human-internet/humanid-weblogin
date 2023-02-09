<form method="post">
    <div class="humanid-logo">
        <div class="humanid-logo__new-number">
            <img src="<?php echo base_url('assets/images/humanid-logo.svg'); ?>" alt="humanID"
                 style="">
        </div>
    </div>

    <div class="humanid-page-title">
        Recover Account
    </div>

    <div class="humanid-content-text humanid-fw-normal">
        <p>Please enter the confirmation code that was sent to your email:</p>
    </div>

    <div class="humanid-form-placement">
        <div class="humanid-form-placement__otp-verification">
            <div class="humanid-form-group">
                <input type="tel" pattern="[0-9]*" class="humanid-input-otp" data-id="1" maxlength="1" name="code_1" autofocus>
            </div>
            <div class="humanid-form-group">
                <input type="tel" pattern="[0-9]*" class="humanid-input-otp" data-id="2" maxlength="1" name="code_2">
            </div>
            <div class="humanid-form-group">
                <input type="tel" pattern="[0-9]*" class="humanid-input-otp" data-id="3" maxlength="1" name="code_3">
            </div>
            <div class="humanid-form-group">
                <input type="tel" pattern="[0-9]*" class="humanid-input-otp" data-id="4" maxlength="1" name="code_4">
            </div>
        </div>
        <span class="timer-text verify-area timer"
              style="display: none;"><?php echo str_replace("{TIME}", '<strong>00:60</strong>', $lang->text->resend); ?></span>
        <input type="hidden" name="remaining" id="remaining">
    </div>
    <div class="humanid-form-placement__otp-resend" style="display:flex;justify-content: center">
        <input type="hidden" name="remaining" id="remaining">
        <a href="javascript:void(0)" class="resend-area timer humanid-text-blue-dark" style="margin: 0;"><?php echo $lang->resend; ?></a>
    </div>
</form>
