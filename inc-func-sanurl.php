<?php
	global $wpdb;

	header('Content-type: application/json');

	$success = $error = false;

	$pid   = !empty($_POST['id'])    ? (int) $_REQUEST['id'] : 0;

	$info = get_post_meta($pid, '_wp_attachment_metadata', true);
	if($info) {
		$bdir = dirname($this->path.$info['file']).'/';
		$fdir = dirname($info['file']).'/';
		$files = array(
			'original' => array(
				'name' => basename($info['file'])
			)
		);
		foreach($info['sizes'] as $name=>$size) {
			$files[$name] = array(
				'name' => $size['file']
			);
		}
		foreach($files as $name=>&$size) {
			$fname = utf8_decode($size['name']);
			$fname = preg_replace('/[^\w\.]/', '-', $fname);
			$size['rename'] = $fname;
		}
		$success = print_r($info, true);
		foreach($files as $name=>$size) {
			if($size['name']!==$size['rename']) {
				rename($bdir.$size['name'], $bdir.$size['rename']);
				if($name=='original') {
					$success = sprintf( __('Image «%1$s» (ID %2$s) renamed as «%3$s»', 'imagecare'), $size['name'], $pid, $size['rename']);
					$info['file'] = $fdir.$size['rename'];
				} else {
					$info['sizes'][$name]['file'] = $size['rename'];
				}
			} else {
				if($name=='original') $success = sprintf( __('Image «%1$s» (ID %2$s) doesn´t change', 'imagecare'), $size['name'], $pid);
				break;
			}
		}
		if(!update_post_meta($pid, '_wp_attachment_metadata', $info)) {
			$error = sprintf( __('File &quot;%1$s&quot; (ID %2$s) failed when updating the database', 'imagecare'), $size['rename'], $pid);
		}
//		$success .= print_r($info, true);
	} else {
		$error = sprintf( __('Image not found (ID %1$s)', 'imagecare'), $pid);
	}

// sleep(10);

	die(json_encode(array(
		'success' => $success,
		'error'   => $error
	)));
?>