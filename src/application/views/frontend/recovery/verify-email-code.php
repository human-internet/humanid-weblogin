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

    <div class="humanid-content-text humanid-fw-normal">
        <p> Please enter the confirmation code that was sent to your email:</p>
    </div>

    <div class="humanid-form-placement">
        <div class="humanid-form-placement__otp-verification">
            <?php for($i=1; $i<=$otpLength; $i++) {?>
                <div class="humanid-form-group">
                    <input type="number" class="humanid-input-otp" data-id="<?php echo $i;?>" maxlength="1" name="code[]" autofocus>
                </div>
            <?php }?>
        </div>
        <?php if ($this->session->flashdata('error_otp')){?>
            <div style="width: 267px;margin: auto;padding: 4px;background: rgba(255, 75, 85, 0.29);font-weight: 500;">
                <?php echo $this->session->flashdata('error_otp');?>
            </div>
        <?php } ?>
        <span class="timer-text verify-area timer"
              style="display: none;"><?php echo str_replace("{TIME}", '<strong>00:60</strong>', $lang->text->resend); ?></span>
        <input type="hidden" name="remaining" id="remaining">
    </div>
    <div class="humanid-form-placement__otp-resend humanid-content-text">
        <a href="<?php echo site_url('recovery/request_email') ?>" class="humanid-text-blue-dark"><?php echo $lang->resend;?></a>
    </div>
</form>
