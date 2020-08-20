<form method="post">
    <div class="humanid-logo">
        <div class="humanid-logo__placement">
            <img src="<?php echo base_url('assets/images/client/greenzone.png');?>" alt="Green Zone">
        </div>
    </div>
    <div class="humanid-page-title">GreenZone will not receive any of your personal information.</div>

    <div class="humanid-content-text">
        <div class="humanid-text-info humanid-text-info-danger">
            <?php if(isset($error_message)):?><p><?php echo $error_message;?></p><?php endif;?>
            <?php echo form_error('phone', '<p>', '</p>');?>
            <?php echo form_error('dialcode', '<p>', '</p>');?>
        </div>
    </div>

    <div class="humanid-form-placement">
        <div class="humanid-form-placement__default">
            <div class="humanid-form-placement__default-main">
                <div class="humanid-form-group">
                    <input type="tel" id="phone" class="humanid-input-default" name="phone">
                    <input type="hidden" name="dialcode" id="dialcode">
                </div>
            </div>
        </div>
        <div class="humanid-form-placement__secure-text">Your phone number is deleted after verification</div>
    </div>
    <div class="humanid-content-text">
        <p><a href="">Learn more</a> about our mission to restore privacy.</p>
    </div>
    <div class="humanid-button humanid-button-vertical">
        <button class="btn-humanid btn-humanid-primary" type="submit">Send SMS With Verification Code</button>
        <button class="btn-humanid btn-humanid-secondary" type="button">New Number? Recover Account</button>
    </div>
</form>