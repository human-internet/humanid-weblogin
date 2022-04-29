<form method="post">
    <div class="humanid-logo">
        <div class="humanid-logo__placement">
            <img src="<?php echo $app['logoUrls']['thumbnail'];?>" alt="<?php echo $app['name'];?>">
        </div>
    </div>
    <div class="humanid-page-title"><?php echo str_replace("{APPNAME}",$app['name'],$lang->text->pageTitleApp);?></div>

    <div class="humanid-content-text">
        <div class="humanid-text-info humanid-text-info-danger">
            <?php if(isset($error_message)):?><p><?php echo $error_message;?></p><?php endif;?>
        </div>
    </div>

    <div class="humanid-form-placement">
        <div class="humanid-form-placement__default">
            <div class="humanid-form-placement__default-main">
                <div class="humanid-form-group">
                    <input type="tel" id="phoneDisplay" class="humanid-input-default" placeholder="812-345-6780" maxlength="17">
                    <input type="hidden" name="dialcode" id="dialcode">
                    <input type="hidden" name="phone" id="phone" value="<?php echo set_value('phone', $phone);?>">
                </div>
            </div>
        </div>
        <div class="humanid-form-placement__secure-text"><?php echo $lang->text->phone;?></div>
    </div>
    <div class="humanid-content-text">
        <p><?php echo str_replace("{LINK}","https://human-id.org/#how-we-protect/",$lang->text->privacy);?></p>
    </div>
    <div class="humanid-button humanid-button-vertical">
        <button class="btn-humanid btn-humanid-primary" type="submit"><?php echo $lang->sendCode;?></button>
        <!--<button class="btn-humanid btn-humanid-secondary" type="button"><?php echo $lang->newAccount;?></button>-->
    </div>
    <div>
        <a href="<?php echo base_url('recovery/new_number') ?>">Got a New Number? Recover Account</a>
    </div>
</form>
