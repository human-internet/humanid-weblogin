<div class="humanid-logo">
    <div class="humanid-logo__new-number">
        <img src="<?php echo base_url('assets/images/humanid-logo.svg'); ?>" alt="humanID"
             style="">
    </div>
</div>

<div class="humanid-content-text" style="margin-bottom: 40px;">
    <p> The phone number +1 617 999 9999 has previously been used to create <br>
        a humanID account. Do you want to log in with this number instead?
    </p>
</div>

<div class="humanid-button humanid-button-vertical" style="display: flex;align-items: center">
    <a href="<?php echo base_url('recovery-exist/instead-login')?>" class="btn-humanid btn-humanid-primary" type="submit" style="width: 340px; margin-bottom: 40px;">YES, LOG ME
        IN INSTEAD
    </a>
    <a href="<?php echo base_url('recovery-exist/switch-number') ?>" class="btn-humanid btn-humanid-primary"
       style="width: 340px; display: block;">No, CONTINUE SWITCHING NUMBERS
    </a>
</div>
