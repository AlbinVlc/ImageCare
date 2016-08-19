<?php

	$avail  = class_exists('ZipArchive');
	$letsgo = !empty($_POST['letsgo']) ? (bool) $_POST['letsgo'] : false;

	if(!$letsgo) {
		if(!$avail) {
			?><p>Sorry, the <strong>ZIP module</strong> is not available in your hosting.</p><?php
		} else {
			global $wpdb;
			$sql1 = "SELECT DATE_FORMAT(post_modified, '%Y-%m') dt FROM {$this->prefix}posts WHERE post_mime_type like 'image/%' GROUP BY DATE_FORMAT(post_modified, '%Y-%m') ORDER BY dt DESC";
			$recs = $wpdb->get_results($sql1, OBJECT);
?>
<p>You can make a complete backup of all your files but if it takes more execution time or more memory than is available, you have a second option so you can download files by year/month<!-- or by taxonomy-->.</p>
<p>It also makes sense to backup only new files if you keep previous downloads.</p>
<form method="post" action="">
	<?php wp_nonce_field('imagecare', 'bup') ?>
	<p><input type="submit" class="button" name="letsgo" value="<?php _e( 'Let´s be prudents', 'bup' ) ?>" /></p>
	<table class="form-table">
	<tbody>
		<tr>
			<th><label>Year-Month</label></th>
			<td>
				<input type="checkbox" name="dtop" value="x" checked /> All <br/>
				<div style="column-count: 4"><?php foreach($recs as $rec) { ?>
				<input type="checkbox" name="dtop[]" value="<?php echo $rec->dt ?>" /> <?php echo $rec->dt ?><br/>
				<?php } ?></div>
			</td>
		</tr><!--tr>
			<th><label>Taxonomy</label></th>
			<td>
				<input type="checkbox" name="taxo" value="x" checked /> All <br/>
			</td>
		</tr-->
	</tbody>
	</table>
</form>
<?php
		}
	} else {
		if($this->check_before_do('bup')) {
			$this->do_backup();
		}
	}
?>