<?php
	global $wpdb;

	header('Content-type: application/json');

	$success = $error = false;
	$size1 = $size2 = $saved = 0;

	$pid   = !empty($_POST['id'])    ? (int) $_REQUEST['id'] : 0;

	$lmt_w = get_option('imagecare_lmt_w', 1024);
	$lmt_h = get_option('imagecare_lmt_h', 1024);
	$lmt_c = $lmt_h / $lmt_w;

	$info = get_post_meta($pid, '_wp_attachment_metadata', true);
	if($info) {
		$file  = $this->path.$info['file'];
		$fname = basename($file);
		$finfo = pathinfo($file);
		$img_o = imagecreatefromjpeg($file);
		$img_w = imagesx($img_o);
		$img_h = imagesy($img_o);
		$img_c = $img_h / $img_w;
		$img_s = filesize($file);
		$new_z = false;
		if($img_w>$lmt_w || $img_h>$lmt_h) {
			if($img_c>$lmt_c) {
				$new_z = '&raquo;';
				$new_h = $lmt_h;
				$new_w = intval($new_h*($img_w/$img_h));
			} elseif($img_c<$lmt_c) {
				$new_z = '&laquo;';
				$new_w = $lmt_w;
				$new_h = intval($new_w*($img_h/$img_w));
			}
		}
		if($new_z) {
			$info['width']  = $new_w;
			$info['height'] = $new_h;
			$new_o = imagecreatetruecolor($new_w, $new_h);
			$res1 = $img_w.'×'.$img_h;
			$res2 = $new_w.'×'.$new_h;
			if(imagecopyresampled($new_o, $img_o, 0, 0, 0, 0, $new_w, $new_h, $img_w, $img_h)) {
				imagedestroy($img_o);
				if(imagejpeg($new_o, $file, 75)) {
					clearstatcache();
					imagedestroy($new_o);
					$new_s = filesize($file);
					$saved = intval(($img_s-$new_s)/1024);
					if(update_post_meta($pid, '_wp_attachment_metadata', $info)) {
						$success = sprintf( __( '&quot;%1$s&quot; (ID %2$s) was successfully from %3$s to %4$s saving %5$s.', 'imagecare' ), $fname, $pid, $res1, $res2, $saved );
					} else {
						$error = sprintf( __('File &quot;%1$s&quot; (ID %2$s) failed when updating the database', 'imagecare'), $fname, $pid);
					}
				} else {
					$error = sprintf( __('File &quot;%1$s&quot; (ID %2$s) failed when saving the file', 'imagecare'), $fname, $pid);
				}
			} else {
				$error = sprintf( __('File &quot;%1$s&quot; (ID %2$s) failed to resample', 'imagecare'), $fname, $pid);
			}
		} else {
			$success = sprintf( __( '&quot;%1$s&quot; (ID %2$s) already had the right size.', 'imagecare' ), $fname, $pid );
		}
	} else {
		$error = sprintf( __('Image not found (ID %1$s)', 'imagecare'), $pid);
	}

// sleep(10);

	die(json_encode(array(
		'success' => $success,
		'error'   => $error,
		'size_b'  => $size1,
		'size_a'  => $size2,
		'saved'   => $saved
	)));
?>