<form method="post">
    <div class="humanid-logo">
        <div class="humanid-logo__placement">
        <img src="<?php echo $app['logoUrls']['thumbnail'];?>" alt="<?php echo $app['name'];?>">
        </div>
    </div>
    <?php if($success):?>
        <div class="humanid-page-title"><?php echo str_replace("{APPNAME}",$app['name'],$lang->text->pageTitleApp);?></div>
        <div class="humanid-content-text">
            <div class="humanid-text-info humanid-text-info-default">
                <p><?php echo $lang->text->welcome;?></p>
                <p><?php echo str_replace("{APPNAME}",$app['name'],$lang->text->continue);?></p>
            </div>
        </div>
        <div class="humanid-button humanid-button-vertical">
            <button class="btn-humanid btn-humanid-primary directed-now" type="button"><?php echo $lang->redirect;?></button>
            <button class="btn-humanid btn-humanid-secondary" type="button"><?php echo str_replace(array("{TIMER}","{APPNAME}"),array('<span class="timer-text"></span>',$app['name']),$lang->text->timer);?></button>
            <input type="hidden" class="directed-link" value="<?php echo $redirectUrl;?>">
        </div>
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
                    <input type="text" class="humanid-input-otp" data-id="1" maxlength="1" name="code_1" autofocus>
                </div>
                <div class="humanid-form-group">
                    <input type="text" class="humanid-input-otp" data-id="2" maxlength="1" name="code_2">
                </div>
                <div class="humanid-form-group">
                    <input type="text" class="humanid-input-otp" data-id="3" maxlength="1" name="code_3">
                </div>
                <div class="humanid-form-group">
                    <input type="text" class="humanid-input-otp" data-id="4" maxlength="1" name="code_4">
                </div>
            </div>
            <div class="humanid-form-placement__otp-resend">
                <span class="timer-text verify-area timer"><?php echo str_replace("{TIME}",'<strong>00:60</strong>',$lang->text->resend);?></span>
                <input type="hidden" name="remaining" id="remaining">
                <a href="<?php echo site_url('login/resend?a='.$app['id'].'&t='.$row['token'].'&lang='.$lang->id);?>" class="resend-area timer" style="display:none;"><?php echo $lang->resend;?></a>
                <a href="<?php echo site_url('login?a='.$app['id'].'&t='.$row['token'].'&lang='.$lang->id);?>"><?php echo $lang->try;?></a>
            </div>
        </div>
    <?php endif;?>
</form>