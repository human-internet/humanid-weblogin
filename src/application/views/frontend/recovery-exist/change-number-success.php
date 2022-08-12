<div class="humanid-modal__overlay active"></div>
<div class="humanid-modal__modal active" id="modal-agree">
    <div class="humanid-modal__modal__main recovery-success">
        <div class="humanid-logo">
            <div class="humanid-logo__placement">
                <img src="<?php echo base_url('images/change-number-success.svg'); ?>"
                     alt="success change password">
            </div>
        </div>
        <p class="humanid-modal__modal__title">Phone number has been changed</p>
        <p class="humanid-fw-normal" style="text-align: center; margin-bottom: 60px;">
            The phone number on your account has been successfully changed, hashed and deleted.
            You can now log into your previous humanID account across all services.
        </p>

        <div class="humanid-modal__modal__footer">
            <div class="action-button">
                <button type="button" class="btn-humanid btn-humanid-primary" data-close="modal-agree"
                        id="close-popup">Return to {clientname}
                </button>
            </div>
        </div>
    </div>
</div>
