<div class="humanid-logo">
    <div class="humanid-logo__placement" style="height: 3.563rem;">
        <img src="<?php echo base_url('assets/images/humanid-basic-vertical.svg'); ?>" alt="humanID"
             style="width: 6.625rem; height: 6.625rem;">
    </div>
</div>

<div class="humanid-content-text" style="margin-bottom: 40px;">
    <p> The phone number <?php echo $phone; ?> has previously been used to create <br>
        a humanID account. Do you want to log in with this number instead?
    </p>
</div>

<div class="humanid-button humanid-button-vertical" style="display: flex;align-items: center">
    <a href="<?php echo base_url('redirect_app') ?>" class="btn-humanid btn-humanid-primary"
       type="submit" style="width: 340px; margin-bottom: 40px;">
        YES, LOG ME IN INSTEAD
    </a>
    <a href="<?php echo base_url('recovery/verify_email') ?>" class="btn-humanid btn-humanid-primary"
       style="width: 340px; display: block;">
        No, CONTINUE SWITCHING NUMBERS
    </a>
</div>
