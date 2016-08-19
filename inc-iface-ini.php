<style>
	input[type=number] { width: 100px; }
</style>

<h2>Top resolution</h2>
<p>Nowadays most users upload images as they come from their mobile or camera. Wordpress always keep the original image file, in order to have the image as large as possible when it needs to scale the image to new resolutions. Therefore is easy to end up storing very heavy files with pointless resolutions. Who really need a 4096x3072px image?</p>
<p>I propose to look for these images and rescale them to a useful but lighter top resolution of your choice.</p>
<form method="post" action="?page=imagecare&tab=anal">
	<?php wp_nonce_field('imagecare', 'maxres') ?>
	<?php
		/*
		$lmt_w = get_option('imagecare_lmt_w', 1024);
		$lmt_h = get_option('imagecare_lmt_h', 1024);
	?>
	<p>
		<label>Max width</label>
		<input type="number" name="lmt_w" value="<?php echo $lmt_w ?>" />
		<label>Max height</label>
		<input type="number" name="lmt_h" value="<?php echo $lmt_h ?>" />
		<input type="submit" class="button hide-if-no-js" name="letsgo" value="<?php _e( 'Crop original images', 'maxres' ) ?>" />
	</p>
	<?php
		*/
	?>
</form>

<h2>Regenerate resolutions</h2>
<p>If you have added a new size or changed the resolution of an existing size, you need to regenerate the files but you don´t need to regenerate them for all the configured sizes.</p>
<p>I offer you a way to regenerate only some sizes.</p>
<form method="post" action="?page=imagecare&tab=regensizes">
	<?php wp_nonce_field('imagecare', 'regensizes') ?>
	<input type="hidden" name="letsgo" value="true" />
	<p>
		<input type="submit" class="button hide-if-no-js" value="<?php _e( 'Regenearte', 'imagecare' ) ?>" />
	</p>
	<noscript><p><em><?php _e( 'You must enable Javascript in order to proceed!', 'imagecare' ) ?></em></p></noscript>
</form>

<h2>Unused resolutions</h2>
<p>Let´s say that your theme defines an image resolution of 320x120 for a size called 'banner' and then you change your theme to another one that defines a new resolution of 320x180 for a size called 'banners_home'. Wordpress doesn´t realize of the change and all old images, which will be used never more, are left in uploads.</p>
<p>I propose to leave the original ones but remove all the scaled versions, and then scale the original for the currently defined sizes.</p>
<form method="post" action="?page=imagecare&tab=cleanregen">
	<?php wp_nonce_field('imagecare', 'cleanregen') ?>
	<input type="hidden" name="letsgo" value="true" />
	<p>
		<input type="submit" class="button hide-if-no-js" value="<?php _e( 'Clean & Regenearte', 'imagecare' ) ?>" />
	</p>
	<noscript><p><em><?php _e( 'You must enable Javascript in order to proceed!', 'imagecare' ) ?></em></p></noscript>
</form>