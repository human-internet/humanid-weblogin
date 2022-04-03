<form method="post" action="<?php echo base_url("recovery/confirmation")?>">
    <div class="humanid-logo">
        <div class="humanid-logo__placement">
            <img src="<?php echo $app['logoUrls']['thumbnail']; ?>" alt="<?php echo $app['name']; ?>">
        </div>
    </div>

    <div class="humanid-page-title">
        Welcome to humanID!
    </div>

    <div class="humanid-content-text">
        <p> Please enter your email address to secure your account </p>
        <div class="humanid-text-info humanid-text-info-default">
            Email recovery can be used to regain access to your account if your phone <br>
            number is changed or lost. Just
            like your phone number, your email will not be <br> stored in readable form.
            <a href="javascript:void(0)" class="humanid-link-blue-light">Learn more</a>
        </div>
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
    <a href="<?php echo base_url('recovery-exist/login')?>" class="humanid-link-red">Recover an existing account instead</a>
    <a href="javascript:void(0)" data-target="modal-agree" class="humanid-link-red">Skip & Risk losing your account</a>
</div>
<div class="humanid-modal__overlay"></div>
<div class="humanid-modal__modal" id="modal-agree">
    <div class="humanid-modal__modal__main">
        <button type="button" class="humanid-modal__modal__close" data-close="modal-agree">X</button>
        <p class="humanid-modal__modal__title">Are you sure?</p>
        <p>Your account could be lost without account <br> recovery.</p>
        <div class="humanid-modal__modal__footer">
            <div class="action-button">
                <button type="button" class="btn-humanid btn-humanid-primary" data-close="modal-agree"  id="close-popup" >No, return to creating email recovery
                </button>
                <button type="button" class="btn-humanid btn-humanid-primary">Yes, skip & risk losing account</button>
            </div>
        </div>
    </div>
</div>
