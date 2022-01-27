<div class="humanid-page-title"><?php echo $lang->errorPage;?></div>

<div class="humanid-content-text">
    <div class="humanid-text-info humanid-text-info-danger">
        <?php if(isset($error_message)):?>
            <p><?php echo $error_message;?></p>
        <?php else:?>
            <p><?php echo $lang->error->found;?></p>
        <?php endif;?>
    </div>
</div>