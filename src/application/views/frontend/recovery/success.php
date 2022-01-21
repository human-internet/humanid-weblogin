<form method="post">
    <div class="humanid-modal__overlay active"></div>
    <div class="humanid-modal__modal active" id="modal-agree">
        <div class="humanid-modal__modal__main recovery-success">
            <div class="humanid-logo">
                <div class="humanid-logo__placement" style="margin: 0">
                    <img src="<?php echo $app['logoUrls']['thumbnail']; ?>" alt="<?php echo $app['name']; ?>">
                </div>
            </div>
            <p class="humanid-modal__modal__title humanid-text-primary">success</p>
            <p>Email recovery has successfully been added to <br> your account.</p>

            <p>Use the email <span class="humanid-text-cyan-dark">{email user provided}</span> if you ever <br> need to
                recover your account in the future.</p>

            <div class="humanid-modal__modal__footer">
                <div class="action-button">
                    <button type="button" class="btn-humanid btn-humanid-primary" data-close="modal-agree"
                            id="close-popup">Continue to application home
                    </button>
                    <button type="button" class="btn-humanid btn-humanid-default">Go back, I need to edit this email
                    </button>
                </div>
            </div>
        </div>
    </div>
