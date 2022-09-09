<form action="<?php echo base_url('recovery-exist/recovery_process')?>" method="post">
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
            <?php if (isset($error_message)): ?>
                <p><?php echo $error_message; ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="humanid-form-placement">
        <div class="humanid-form-placement__email-form">
            <div class="humanid-form-placement__email-main" style="max-width: 500px">
                <div class="humanid-form-group">
                    <input type="email" class="humanid-input-default" name="email"
                           placeholder="Your Recovery Email" autofocus>
                </div>
            </div>
        </div>
    </div>
    <div class="humanid-button humanid-button-vertical" style="display: flex;align-items: center">
        <button class="btn-humanid btn-humanid-primary" type="submit" style="max-width: 500px">ENTER</button>
    </div>

    <div class="humanid-content-text">
        <a href="javascript:void(0)" data-target="modal-cannot-recovery">
            <b class="humanid-text-blue-dark">
                I did not set up a recovery email, or <br> cannot access it anymore
            </b>
        </a>
    </div>
</form>
<a href="javascript:void(0)" id="b-modal-agree" data-target="modal-agree"><b class="humanid-text-blue-dark"></b></a>

<div class="humanid-modal__overlay"></div>
<div class="humanid-modal__modal" id="modal-cannot-recovery">
    <div class="humanid-modal__modal__main" style="padding: 0 40px;padding-top: 70px;">
        <button type="button" class="humanid-modal__modal__close" data-close="modal-cannot-recovery">X</button>
        <p>
            Unfortunately, you can’t recover your account <br>
            without an email.
            You can create a new account with the phone <br>
            number you just entered instead.
        </p>
        <div class="humanid-modal__modal__footer">
            <div class="action-button">
                <a href="<?php echo base_url('redirect_app'); ?>" class="btn-humanid btn-humanid-primary directed-now" data-close="modal-agree"
                   id="close-popup" style="margin-bottom: 30px;">
                    Login with new account
                </a>
                <a href="<?php echo $cancelLoginUrl; ?>" style="display: block;padding-bottom: 15px;" data-close="modal-cannot-recovery">
                    Cancel Login
                </a>
            </div>
        </div>
    </div>
</div>

<div class="humanid-modal__modal" id="modal-agree">
    <div class="humanid-modal__modal__main recovery-success">
        <p class="humanid-modal__modal__title" style="margin-bottom: 40px;line-height: 1.5;">
            We couldn't identify
            <br>
            your old account
        </p>

        <div class="humanid-modal__modal__footer">
            <div class="action-button">
                <button type="button" class="humanid-modal__modal__close" data-close="modal-agree">X</button>
                <a href="<?php echo $redirectUrl ?>" id="t-modal-cannot-recovery" class="btn-humanid btn-humanid-primary"
                   style="position: relative;right: 0;margin-bottom:25px;">
                    Create an Account with <br>
                    your new number instead
                </a>
                <button type="button" data-close="modal-agree"
                        class="humanid-modal__modal__close btn-humanid btn-humanid-primary"
                        style="position: relative;right: 0;margin-bottom:25px;"
                        id="close-popup">Try a different Number/Email</button>
            </div>
        </div>
    </div>
</div>

<?php
if($invalidEmail){
    ?>
    <script>
        window.addEventListener('load', function() {
            document.getElementById('b-modal-agree').click();
        });
    </script>
    <?php
}
?>
