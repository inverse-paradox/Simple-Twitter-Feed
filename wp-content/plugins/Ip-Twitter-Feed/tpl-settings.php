<div class="wrap">
    <?php screen_icon(); ?>
    <h2>Support Settings</h2>			
	<form method="post">
		<?php $settings_page = 'ip_twitter';?>
        <?php settings_fields($settings_page);?>
        <?php do_settings_sections($settings_page);?>
        <?php submit_button(); ?>
	</form>
</div>