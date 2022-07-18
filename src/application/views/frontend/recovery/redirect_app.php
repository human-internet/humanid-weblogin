<form method="post">
    <div class="humanid-logo">
        <div class="humanid-logo__placement">
            <img src="<?php echo $app['logoUrls']['thumbnail'];?>" alt="<?php echo $app['name'];?>">
        </div>
    </div>
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
    <div class="humanid-content-link center">
        <a href="<?php echo base_url("recovery/create");?>" class="humanid-link-red">Recover an existing account instead</a>
    </div>
</form>
