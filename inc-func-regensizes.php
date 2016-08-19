<?php
	global $wpdb;

	header('Content-type: application/json');

	$success = $error = false;
	$size1 = $size2 = $saved = 0;

	$pid   = !empty($_POST['id'])    ? (int) $_REQUEST['id'] : 0;
	$sizes = !empty($_POST['sizes']) ? explode(chr(44), $_REQUEST['sizes']) : 'x';

	$org = $info = get_post_meta($pid, '_wp_attachment_metadata', true);
	if($info) {
		$file  = $this->path.$info['file'];
		$fname = basename($file);
		$finfo = pathinfo($file);
		$zizes = $this->get_image_sizes();
		if($sizes=='x') {
			$arr = $zizes;
		} else {
			$arr = array();
			foreach($zizes as $name=>$item) {
				if(in_array($name, $sizes)) $arr[$name] = $item;
			}
		}
		if(!empty($arr)) {
			foreach($arr as $name=>$values) {
				$size1 += filesize($finfo['dirname'].'/'.$info['sizes'][$name]['file']);
			}
			$amt = count($arr);
			$editor = wp_get_image_editor($file);
			if(!is_wp_error($editor)) {
				$temp = $editor->multi_resize($arr);
				foreach($arr as $name=>$values) {
					$info['sizes'][$name] = $temp[$name];
					$size2 += filesize($finfo['dirname'].'/'.$info['sizes'][$name]['file']);
				}
				$saved = intval(($size1-$size2)/1024);
				if($org===$info) {
					$success = sprintf( __( '&quot;%1$s&quot; (ID %2$s) already had the right size.', 'imagecare' ), $fname, $pid );
				} elseif(wp_update_attachment_metadata( $pid, $info )) {
					$success .= sprintf( __( '&quot;%1$s&quot; (ID %2$s) was successfully regenerated %3$s files from %4$s Kb to %5$s Kb saving %6$s Kb.', 'imagecare' ), $fname, $pid, $amt, intval($size1/1024), intval($size2/1024), $saved );
				} else {
					$error = sprintf( __('File &quot;%1$s&quot; (ID %2$s) failed when updating the database', 'imagecare'), $fname, $pid);
				}
			} else {
				$error = sprintf( __('File &quot;%1$s&quot; (ID %2$s) failed when initializing the editor', 'imagecare'), $fname, $pid);
			}
		} else {
			$error = sprintf( __('No valid sizes choosen', 'imagecare')).print_r($sizes, true).print_r($zizes, true).print_r($arr, true);
		}
	} else {
		$error = sprintf( __('Image not found (ID %1$s)', 'imagecare'), $pid);
	}

// sleep(5);

	die(json_encode(array(
		'success' => $success,
		'error'   => $error,
		'size_b'  => $size1
	)));
?>