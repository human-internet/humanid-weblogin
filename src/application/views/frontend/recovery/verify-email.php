<form action="" method="post">
    <div class="humanid-logo">
        <div class="humanid-logo__placement">
            <img src="<?php echo base_url('assets/images/humanid-logo.svg'); ?>" alt="humanID"
                 style="">
        </div>
    </div>

    <div class="humanid-page-title">
        Recover Account
    </div>

    <div class="humanid-content-text humanid-fz-18 humanid-fw-normal">
        <p>Please enter your old phone number and recovery email</p>
    </div>

    <div class="humanid-form-placement">
        <div class="humanid-form-placement__default" style="margin-bottom: 15px;">
            <div class="humanid-form-placement__default-main" style="max-width: 500px">
                <div class="humanid-form-group">
                    <input type="tel" id="phoneDisplay" class="humanid-input-default" placeholder="812-345-6780"
                           maxlength="17">
                    <input type="hidden" name="dialcode" id="dialcode">
                    <input type="hidden" name="phone" id="phone" value="">
                </div>
            </div>
        </div>
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
        <button class="btn-humanid btn-humanid-primary" type="submit" style="width: 250px">Get Code</button>
    </div>
    <div class="humanid-content-text">
        <a href="javascript:void(0)" data-target="modal-cannot-recovery"><b class="humanid-text-blue-dark">I did not set up a recovery email, or <br> cannot access it anymore</b></a>
    </div>
</form>
<div class="humanid-modal__overlay"></div>
<div class="humanid-modal__modal" id="modal-cannot-recovery">
    <div class="humanid-modal__modal__main" style="padding: 0 40px;padding-top: 70px;">
        <button type="button" class="humanid-modal__modal__close" data-close="modal-cannot-recovery">X</button>
        <p>
            Unfortunately, you can’t recover your account <br>
            without an email. You can create a new account with the phone <br>
            number you just entered instead.
        </p>
        <div class="humanid-modal__modal__footer">
            <div class="action-button">
                <button type="button" class="btn-humanid btn-humanid-primary" data-close="modal-agree" id="close-popup" style="margin-bottom: 20px;">
                    login with new account
                </button>
                <a href="javascript:void(0)" class="" data-close="modal-cannot-recovery">Cancel Login </a>
            </div>
        </div>
    </div>
</div>
