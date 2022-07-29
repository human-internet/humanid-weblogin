<form action="<?php echo base_url('recovery-exist/verification') ?>" method="post">
    <div class="humanid-logo">
        <div class="humanid-logo__new-number">
            <img src="<?php echo base_url('assets/images/humanid-logo.svg'); ?>" alt="humanID"
                 style="">
        </div>
    </div>

    <div class="humanid-content-text humanid-fw-normal humanid-fz-18">
        <p>
            The maximum number of allowed account recovery <br> attempts has been exceeded.
            <br>
            <br>

            To protect the account security of our users, we have <br>
            blocked your number from using account recovery to <br>
            transfer an account in the future. However, you can still <br>
            add account recovery if you choose to create a new <br>
            account.
        </p>
    </div>


    <div class="humanid-button humanid-button-vertical">
        <a href="<?php echo base_url('recovery/add')?>" class="btn-humanid btn-humanid-primary" type="submit">Create a new account with
            your new number
        </a>
    </div>
</form>
