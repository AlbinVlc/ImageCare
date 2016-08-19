<?php
	global $wpdb;

	header('Content-type: application/json');

	$success = $error = false;
	$size1 = $size2 = $saved = 0;

	$pid   = !empty($_POST['id'])    ? (int) $_REQUEST['id'] : 0;

	$org = $info = get_post_meta($pid, '_wp_attachment_metadata', true);
	if($info) {
		$path  = $this->path.dirname($info['file']).'/';
		$file  = $this->path.$info['file'];
		$fname = basename($file);
		$finfo = pathinfo($file);
		$files = glob($path.$finfo['filename'].'-*.'.$finfo['extension']); // TODO add a safer filter
		$ok    = true;
		$amt1  = count($files);
		foreach($files as $item) {
			$size1 += filesize($item);
			if(!($ok = unlink($item))) break;
		}
		if($ok) {
			$info['sizes'] = array();
			$metadata = wp_generate_attachment_metadata($pid, $file);
			if(is_wp_error($metadata)) {
				$error = sprintf( __('Unable to regenerate metadata (ID %1$s «%2$s»)', 'imagecare'), $pid, $metadata->get_error_message());
			} elseif(empty($metadata)) {
				$error = sprintf( __('Unable to regenerate metadata (ID %1$s «Unknown reason»)', 'imagecare'), $pid);
			} else {
				if($org===$metadata) {
					$success = sprintf( __( '&quot;%1$s&quot; (ID %2$s) already had the right size.', 'imagecare' ), $fname, $pid );
				} elseif(wp_update_attachment_metadata( $pid, $metadata )) {
					$files = glob($path.$finfo['filename'].'-*.'.$finfo['extension']);
					$amt2  = count($files);
					foreach($files as $item) {
						$size2 += filesize($item);
					}
					$saved = intval(($size1-$size2)/1024);
					$success .= sprintf( __( '&quot;%1$s&quot; (ID %2$s) was successfully regenerated from %3$s files (%4$s Kb) to %5$s files (%6$s Kb) saving %7$s Kb.', 'imagecare' ), $fname, $pid, $amt1, intval($size1/1024), $amt2, intval($size2/1024), $saved );
				} else {
					$error = sprintf( __('File &quot;%1$s&quot; (ID %2$s) failed when updating the database', 'imagecare'), $fname, $pid);
				}
			}
		} else {
			$error = sprintf( __('Unable to remove all the scaled images', 'imagecare'));
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