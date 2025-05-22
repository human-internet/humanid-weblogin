<form method="post">
    <div class="humanid-logo">
        <div class="humanid-logo__placement">
            <img src="<?php echo $app->logoUrls->thumbnail ;?>" alt="<?php echo $app->name ;?>">
        </div>
    </div>
    <div class="humanid-page-title"><?php echo str_replace("{APPNAME}", $app->name, $lang->text->pageTitleApp);?></div>

    <div class="humanid-form-placement">
        <div class="humanid-form-placement__default">
            <div class="humanid-form-placement__default-main">
                <div class="humanid-form-group">
                    <input type="tel" id="phoneDisplay" class="humanid-input-default <?php echo isset($phone_error) ? 'humanid-input-error' : ''; ?>" placeholder="812-345-6780" maxlength="17">
                    <input type="hidden" name="dialcode" id="dialcode">
                    <input type="hidden" name="phone" id="phone" value="<?php echo set_value('phone', $phone);?>">
                    <?php if(isset($error_message)): ?>
                        <div class="humanid-error-message"><?php echo $error_message; ?></div>
                    <?php endif; ?>
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
    <div class="humanid-content-text humanid-content-link">
        <a href="https://docs.human-id.org/privacy-policy" target="_blank"><?php echo $lang->text->policy;?></a>
        <a href="https://docs.human-id.org/sms-terms-and-conditions" target="_blank"><?php echo $lang->text->tnc;?></a>
    </div>
    <div>
        <!--<a href="<?php /*echo base_url('recovery/new_number') */?>">Got a New Number? Recover Account</a>-->
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the phone input with the existing humanID.js functionality
        humanid.formLogin('<?php echo set_value('phone', $phone);?>', ['us', 'gb', 'ca']);
        
        <?php if(!empty($phone) && isset($error_message)): ?>
        // After initialization, trigger formatting by simulating user interaction
        setTimeout(function() {
            var phoneDisplay = document.getElementById('phoneDisplay');
            // Simulate a keyup event to trigger the phone formatting
            var event = new Event('keyup');
            phoneDisplay.dispatchEvent(event);
            console.log('Triggered phone formatting');
        }, 100);
        <?php endif; ?>
    });
</script>
