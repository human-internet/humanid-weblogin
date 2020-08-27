<div class="humanid-page-title">Error Page</div>

<div class="humanid-content-text">
    <div class="humanid-text-info humanid-text-info-danger">
        <?php if(isset($error_message)):?>
            <p><?php echo $error_message;?></p>
        <?php else:?>
            <p>Error not found</p>
        <?php endif;?>
    </div>
</div>