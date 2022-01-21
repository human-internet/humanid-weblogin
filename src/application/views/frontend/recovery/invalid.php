<form method="post">
    <div class="humanid-logo">
        <div class="humanid-logo__placement">
            <img src="<?php echo base_url('assets/images/humanid-logo.svg'); ?>" alt="humanID"
                 style="">
        </div>
    </div>

    <div class="humanid-page-title">
        Welcome to humanID!
    </div>

    <div class="humanid-content-text humanid-fz-16 humanid-fw-normal">
        <p> Please enter your email address to secure your account </p>
        <p>Email recovery can be used to regain access to your account if your phone <br>
            number is changed or lost. Just like your phone number, your email will not be <br>stored in readable form.
            <a href="javascript:void(0)">Learn more</a>
        </p>
    </div>

    <div class="humanid-form-placement">
        <div class="humanid-form-placement__email-form">
            <div class="humanid-form-placement__email-main">
                <div class="humanid-form-group">
                    <input type="email" class="humanid-input-default" name="email" placeholder="Your email address"
                           autofocus>
                </div>
            </div>
        </div>

        <div class="humanid-form-placement__email-form">
            <div class="humanid-form-placement__email-main">
                <div class="humanid-button humanid-button-vertical">
                    <!-- TODO Changes-->
                    <button class="btn-humanid btn-humanid-primary"
                            type="submit">Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="humanid-content-link">
    <a href="javascript:void(0)" class="humanid-link-red">Recover an existing account instead</a>
    <a href="javascript:void(0)" data-target="modal-agree" class="humanid-link-red">Skip & Risk losing your account</a>
</div>
