<form method="post">
    <div class="humanid-logo">
        <div class="humanid-logo__placement">
            <img src="<?php echo base_url('assets/images/client/greenzone.png');?>" alt="Green Zone">
        </div>
    </div>
    <div class="humanid-page-title">Websites and Apps will not receive any of your personal information.</div>

    <div class="humanid-content-text">
        <div class="humanid-text-info humanid-text-info-danger">
            <?php if(isset($error_message)):?><p><?php echo $error_message;?></p><?php endif;?>
        </div>
    </div>

    <div class="humanid-form-placement">
        <div class="humanid-form-placement__default">
            <div class="humanid-form-placement__default-main">
                <div class="humanid-form-group">
                    <input type="tel" id="phoneDisplay" class="humanid-input-default" placeholder="812-345-678" maxlength="14">
                    <input type="hidden" name="dialcode" id="dialcode">
                    <input type="hidden" name="phone" id="phone" value="<?php echo set_value('phone', $phone);?>">
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
        <!--<button class="btn-humanid btn-humanid-secondary" type="button">New Number? Recover Account</button>-->
    </div>
</form>