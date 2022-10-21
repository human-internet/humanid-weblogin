<form method="post">
    <div class="humanid-logo">
        <div class="humanid-logo__placement">
        <img src="<?php echo $app->logoUrls->thumbnail;?>" alt="<?php echo $app->name; ?>">
        </div>
    </div>
    <?php if($success):?>
        <div class="humanid-page-title"><?php echo str_replace("{APPNAME}",$app->name,$lang->text->pageTitleApp);?></div>
        <div class="humanid-content-text">
            <div class="humanid-text-info humanid-text-info-default">
                <p><?php echo $lang->text->welcome;?></p>
                <p><?php echo str_replace("{APPNAME}",$app->name,$lang->text->continue);?></p>
            </div>
        </div>
        <div class="humanid-button humanid-button-vertical">
            <button class="btn-humanid btn-humanid-primary directed-now" type="button"><?php echo $lang->redirect;?></button>
            <button class="btn-humanid btn-humanid-secondary" type="button"><?php echo str_replace(array("{TIMER}","{APPNAME}"),array('<span class="timer-text"></span>',$app->name),$lang->text->timer);?></button>
            <input type="hidden" class="directed-link" value="<?php echo $redirectUrl;?>">
        </div>
        <?php if(!$hasSetupRecovery && $accountRecovery === true) { ?>
                <div class="humanid-content-link center">
                    <a href="<?php echo $redirectSetRecoveryEmail;?>" class="humanid-link-red">Recover an existing account instead</a>
                </div>
        <?php } ?>
    <?php else:?>
        <div class="humanid-page-title"><?php echo $lang->verify;?></div>
        <div class="humanid-content-text">
            <div class="humanid-text-info humanid-text-info-default">
                <p><?php echo str_replace(array("{COUNTRYCODE}","{PHONE}"),array($row['dialcode'], $display_phone),$lang->text->verifyCode);?></p>
                <p><?php echo $lang->text->afterSuccessful;?></p>
            </div>
            <div class="humanid-text-info humanid-text-info-danger">
                <?php if(isset($error_message) && !empty($error_message)):?><p><?php echo $error_message;?></p><?php endif;?>
            </div>
        </div>
        <div class="humanid-form-placement">
            <div class="humanid-form-placement__otp-verification">
                <div class="humanid-form-group">
                    <input type="number" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                           maxlength="1" class="humanid-input-otp" data-id="1" name="code_1" autofocus
                           autocomplete="off">
                </div>
                <div class="humanid-form-group">
                    <input type="number" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                           maxlength="1" class="humanid-input-otp" data-id="2" name="code_2" autocomplete="off">
                </div>
                <div class="humanid-form-group">
                    <input type="number" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                           maxlength="1" class="humanid-input-otp" data-id="3" name="code_3" autocomplete="off">
                </div>
                <div class="humanid-form-group">
                    <input type="number" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
                           maxlength="1" class="humanid-input-otp" data-id="4" name="code_4" autocomplete="off">
                </div>
            </div>
            <span class="timer-text verify-area timer" style="display: none;"><?php echo str_replace("{TIME}",'<strong>00:60</strong>',$lang->text->resend);?></span>
            <input type="hidden" name="remaining" id="remaining">
            <div class="humanid-form-placement__link">
                <div class="humanid-form-placement__link__wrapper">
                    <a href="<?php echo site_url('login?a='.$app->id.'&t='.$row['token'].'&lang='.$lang->id.'&priority_country='.$pc->code);?>" class="humanid-link-blue-light">
                        <?php echo $lang->try;?>
                    </a>
                    <a href="<?php echo site_url('login/resend?a='.$app->id.'&t='.$row['token'].'&lang='.$lang->id);?>" class="humanid-link-blue-light"><?php echo $lang->resend;?></a>
                    <a href="<?php echo base_url('recovery/new_number') ?>" class="humanid-link-blue-light">Recover Existing Account</a>
                </div>
            </div>
        </div>
    <?php endif;?>
</form>
