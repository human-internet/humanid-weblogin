<div class="humanid-logo">
    <div class="humanid-logo__new-number">
        <img src="<?php echo base_url('assets/images/humanid-logo.svg'); ?>" alt="humanID"
             style="">
    </div>
</div>

<div class="humanid-content-text" style="margin-bottom: 40px;">
    <p class="humanid-fw-semi-bold">
        The phone number <?php echo "(+$dialcode) $phone";  ?> has previously been used to <br>
        create a humanID account, but gone inactive. What are you trying to do?
    </p>
</div>

<div class="humanid-button humanid-button-vertical" style="display: flex;align-items: center">
    <a href="<?php echo base_url('recovery/add'); ?> " class="btn-humanid btn-humanid-primary" type="submit" style="width: 370px; margin-bottom: 40px;">
        I’m creating a new account
    </a>
    <a href="<?php echo base_url('recovery-exist/recovery') ?>" class="btn-humanid btn-humanid-default"
       style="width: 370px; display: block;margin-bottom: 20px;">Use Email recovery to LOGIN AGAIN</a>
</div>

<div class="humanid-content-link" style="justify-content: center;">
    <a href="<?php echo base_url('recovery-exist/switch-number');?>" class="humanid-link-blue-light">
        I’m trying to move another account to this number
    </a>
</div>
