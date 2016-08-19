<?php
	global $wpdb;

	$dtop = isset($_POST['dtop']) ? $_POST['dtop'] : false;
	if($dtop!='x') {
		$dtop = implode(chr(44), array_map(array($this, 'safeYearMonth'), $dtop));
		$sql1 = "SELECT meta_value FROM {$this->prefix}posts p INNER JOIN {$this->prefix}postmeta pm ON p.ID=pm.post_id AND meta_key='_wp_attachment_metadata' WHERE DATE_FORMAT(post_modified, '%Y-%m') IN ({$dtop})";
	} else {
		$sql1 = "SELECT meta_value FROM {$this->prefix}posts p INNER JOIN {$this->prefix}postmeta pm ON p.ID=pm.post_id AND meta_key='_wp_attachment_metadata'";
	}
	$recs = $wpdb->get_results($sql1, OBJECT);
	if(!empty($recs)) {
		$bup_path = $this->path.'uploads.zip';
		$bup_url  = $this->url.'uploads.zip';
		$zip = new ZipArchive();
		if($zip->open($bup_path, ZIPARCHIVE::CREATE)) {
			foreach($recs as $rec) {
				$rec = unserialize($rec->meta_value);
				$zip->addFile($this->path.$rec['file'], $rec['file']);
				$dir = dirname($rec['file']).'/';
				foreach($rec['sizes'] as $size) {
					$zip->addFile($this->path.$dir.$size['file'], $dir.$size['file']);
				}
			}
			$zip->close();
		}
		?><p><a href="<?php echo $bup_url ?>">Download ZIP</a></p><?php
	}

?>