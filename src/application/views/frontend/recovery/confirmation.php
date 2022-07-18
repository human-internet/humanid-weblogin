<form method="post" action="<?php echo base_url('recovery/confirmation_process')?>">
    <div class="humanid-modal__overlay active"></div>
    <div class="humanid-modal__modal active" id="modal-agree">
        <div class="humanid-modal__modal__main recovery-success">
            <div class="humanid-logo">
                <div class="humanid-logo__placement" style="margin: 0">
                    <img src="<?php echo $app['logoUrls']['thumbnail']; ?>" alt="<?php echo $app['name']; ?>">
                </div>
            </div>
            <p class="humanid-modal__modal__title humanid-text-primary humanid-modal__title">SUCCESS!</p>
            <p class="humanid-modal__description first">Email recovery has successfully been added to <br> your account.</p>

            <input type="hidden" name="email" value="<?php echo $email ?? "#"; ?>">

            <p class="humanid-modal__description second">Use the email <b class="humanid-text-cyan-dark"><?php echo $email ?? ''; ?></b> if you ever <br> need to
                recover your account in the future.</p>

            <div class="humanid-modal__modal__footer">
                <div class="action-button">
                    <button type="submit" class="btn-humanid btn-humanid-primary" data-close="modal-agree"
                            id="close-popup">Continue to application home
                    </button>
                    <a href="<?php echo $redirectSetRecoveryEmail ?? '#' ; ?>" class="btn-humanid btn-humanid-default">Go back, I need to edit this email
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
