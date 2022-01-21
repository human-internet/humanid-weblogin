<div class="humanid-modal__overlay active"></div>
<div class="humanid-modal__modal" id="modal-cannot-recovery" style="z-index: 2000;">
    <div class="humanid-modal__modal__main" style="padding: 0 40px;padding-top: 70px;padding-bottom: 20px;">
        <button type="button" class="humanid-modal__modal__close" data-close="modal-cannot-recovery">X</button>
        <p>
            Unfortunately, you can’t recover your account <br>
            without an email. You can create a new account with the phone <br>
            number you just entered instead.
        </p>
        <div class="humanid-modal__modal__footer">
            <div class="action-button">
                <button type="button" class="btn-humanid btn-humanid-primary" data-close="modal-agree" id="close-popup"
                        style="margin-bottom: 20px;">
                    login with new account
                </button>
                <a href="javascript:void(0)" class="" data-close="modal-cannot-recovery">Cancel Login </a>
            </div>
        </div>
    </div>
</div>
<div class="humanid-modal__modal active" id="modal-agree">
    <div class="humanid-modal__modal__main recovery-success">
        <p class="humanid-modal__modal__title" style="margin-bottom: 40px;">
            We couldn’t identify
            <br>
            your old account
        </p>

        <div class="humanid-modal__modal__footer">
            <div class="action-button">
                <button type="button" data-target="modal-cannot-recovery" class="btn-humanid btn-humanid-primary"
                        >
                    create an ACCOUNT with your new NUMBER INSTEAD
                </button>
                <a href="<?php echo base_url('recovery-exist/switch-number') ?>"
                   class="btn-humanid btn-humanid-primary" style="display:block;"
                   id="close-popup">Try a different number/EMAIL</a>
            </div>
        </div>
    </div>
</div>
