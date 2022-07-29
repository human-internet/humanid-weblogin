<form action="<?php echo base_url('recovery-exist/verification')?>" method="post">
    <div class="humanid-logo">
        <div class="humanid-logo__new-number">
            <img src="<?php echo base_url('assets/images/humanid-logo.svg'); ?>" alt="humanID"
                 style="">
        </div>
    </div>

    <div class="humanid-page-title" style="margin-bottom: 10px;">
        Recovering an Inactive Account
    </div>

    <div class="humanid-content-text humanid-fz-16 humanid-fw-semi-bold humanid-text-primary">
        <p> Please enter your recovery email
        </p>
    </div>

    <div class="humanid-content-text">
        <div class="humanid-text-info humanid-text-info-danger">
            <?php if (isset($error_message)): ?><p><?php echo $error_message; ?></p><?php endif; ?>
        </div>
    </div>

    <div class="humanid-form-placement">
        <div class="humanid-form-placement__email-form">
            <div class="humanid-form-placement__email-main" style="max-width: 500px">
                <div class="humanid-form-group">
                    <input type="email" class="humanid-input-default" name="email" placeholder="Your email address"
                           autofocus>
                </div>
            </div>
        </div>
    </div>
    <div class="humanid-button humanid-button-vertical" style="display: flex;align-items: center">
        <button class="btn-humanid btn-humanid-primary" type="submit" style="max-width: 500px">Login</button>
    </div>
</form>
