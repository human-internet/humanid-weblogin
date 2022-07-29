<form action="<?php echo base_url('recovery-exist/verification')?>" method="post">
    <div class="humanid-logo">
        <div class="humanid-logo__new-number">
            <img src="<?php echo base_url('assets/images/humanid-logo.svg'); ?>" alt="humanID"
                 style="">
        </div>
    </div>

    <div class="humanid-content-text">
        <p> humanID is the independent & open online ID - giving back privacy, and the internet, to humans.
            <br>
            <a href="https://human-internet.org" class="humanid-link-primary">Learn About Our Mission</a>
        </p>
    </div>

    <div class="humanid-page-title">
        Recover Account
    </div>

    <div class="humanid-content-text humanid-fz-16 humanid-fw-normal">
        <p> Let’s transfer your humanID to a new number. First, let’s verify your <b class="humanid-fw-bold">new</b>
            phone number:
        </p>
    </div>

    <div class="humanid-content-text">
        <div class="humanid-text-info humanid-text-info-danger">
            <?php if (isset($error_message)): ?><p><?php echo $error_message; ?></p><?php endif; ?>
        </div>
    </div>

    <div class="humanid-form-placement">
        <div class="humanid-form-placement__default">
            <div class="humanid-form-placement__default-main">
                <div class="humanid-form-group">
                    <input type="tel" id="phoneDisplay" class="humanid-input-default" placeholder="812-345-6780"
                           maxlength="17">
                    <input type="hidden" name="dialcode" id="dialcode">
                    <input type="hidden" name="phone" id="phone" value="">
                </div>
            </div>
        </div>
    </div>
    <div class="humanid-content-text">
        No personal information is ever stored beyond this recovery process.
        <a href="javascript:void(0)"><b>Learn More</b></a>
    </div>
    <div class="humanid-button humanid-button-vertical" style="display: flex;align-items: center">
        <button class="btn-humanid btn-humanid-primary" type="submit" style="width: 250px">Login</button>
    </div>
</form>
