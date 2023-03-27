<form method="post" id="redirect-app" action="<?php echo site_url('redirect_now') ?>">
    <div class="humanid-logo">
        <div class="humanid-logo__placement">
            <img src="<?php echo $app->logoUrls->thumbnail; ?>" alt="<?php echo $app->name; ?>">
        </div>
    </div>
    <div class="humanid-page-title"><?php echo str_replace("{APPNAME}",$app->name, $lang->text->pageTitleApp);?></div>
    <div class="humanid-content-text">
        <div class="humanid-text-info humanid-text-info-default">
            <p><?php echo $lang->text->welcome;?></p>
            <p><?php echo str_replace("{APPNAME}",$app->name, $lang->text->continue);?></p>
        </div>
    </div>

    <div class="humanid-button humanid-button-vertical">
        <button class="btn-humanid btn-humanid-primary" type="submit"><?php echo $lang->redirect;?></button>
        <button class="btn-humanid btn-humanid-secondary" type="button">
            <?php echo str_replace(["{TIMER}","{APPNAME}"],
                ['<span class="timer-text"></span>', $app->name], $lang->text->timer);?>
        </button>
        <input type="hidden" name="redirectUrl" class="directed-link" value="<?php echo $redirectUrl;?>">
    </div>
    <?php
    /*
        if (isset($hasSetupRecovery) && $hasSetupRecovery === false): ?>
        <div class="humanid-content-link center">
            <a href="<?php echo base_url('recovery/create');?>" class="humanid-link-red">
                Add email recovery to your account
            </a>
        </div>
    <?php endif;
    */
    ?>
</form>
