<form method="post">
    <div class="humanid-logo">
        <div class="humanid-logo__placement">
            <img src="<?php echo base_url('assets/images/client/greenzone.png');?>" alt="Green Zone">
        </div>
    </div>
    <?php if($success):?>
        <div class="humanid-page-title">GreenZone will not receive any of your personal information.</div>
        <div class="humanid-content-text">
            <div class="humanid-text-info humanid-text-info-default">
                <p>Welcome, you have successfully logged in.</p>
                <p>You will be directed to the previous webpage to continue</p>
            </div>
        </div>
        <div class="humanid-button humanid-button-vertical">
            <button class="btn-humanid btn-humanid-primary directed-now" type="button">Redirect Now</button>
            <button class="btn-humanid btn-humanid-secondary" type="button">You will be directed to a page in <span class="timer-text"></span> seconds</button>
            <input type="hidden" class="directed-link" value="<?php echo site_url('client/success?token='.$exchangeToken);?>">
        </div>
    <?php else:?>
        <div class="humanid-page-title">Verify Your Phone Number</div>
        <div class="humanid-content-text">
            <div class="humanid-text-info humanid-text-info-default">
                <p>Please enter the 4 digit code you received as SMS to <strong>+<?php echo $row['dialcode']?> <?php echo $row['phone']?></strong>.</p>
                <p>After successful verification, your number will be deleted permanently and only a random identifier will be stored. </p>
            </div>
            <div class="humanid-text-info humanid-text-info-danger">
                <?php if(isset($error_message)):?><p><?php echo $error_message;?></p><?php endif;?>
                <?php echo form_error('code_1', '<p>', '</p>');?>
                <?php echo form_error('code_2', '<p>', '</p>');?>
                <?php echo form_error('code_3', '<p>', '</p>');?>
                <?php echo form_error('code_4', '<p>', '</p>');?>
            </div>
        </div>

        <div class="humanid-form-placement">
            <div class="humanid-form-placement__otp-verification">
                <div class="humanid-form-group">
                    <input type="text" class="humanid-input-otp" data-id="1" maxlength="1" name="code_1" autofocus>
                </div>
                <div class="humanid-form-group">
                    <input type="text" class="humanid-input-otp" data-id="2" maxlength="1" name="code_2">
                </div>
                <div class="humanid-form-group">
                    <input type="text" class="humanid-input-otp" data-id="3" maxlength="1" name="code_3">
                </div>
                <div class="humanid-form-group">
                    <input type="text" class="humanid-input-otp" data-id="4" maxlength="1" name="code_4">
                </div>
            </div>
            <div class="humanid-form-placement__otp-resend">
                <span class="timer-text">Resend code in <strong>00:23</strong></span>
                <a href="<?php echo site_url('login?token='.$row['token']);?>">Try Different Number</a>
                <input type="hidden" class="directed-link" value="<?php echo site_url('login/resend?token='.$row['token']);?>">
            </div>
        </div>
    <?php endif;?>
</form>