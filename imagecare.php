<?php

	/**
	 * Plugin Name: ImageCare
	 * Plugin URI: http://wordpress.org/plugins/imagecare/
	 * Description: It takes care about the space taken by the images.
	 * Author: Albin Soft
	 * Version: 1.0
	 * Author URI: http://www.albinsoft.es/
	 **/

class ImageCare {


	private $prefix;
	private $menu_id;

	private $anal = array();

	public function __construct() {
		global $wpdb;
		$this->prefix = $wpdb->prefix;
		$this->capability = apply_filters('imagecare_cap', 'manage_options');

		$this->udir = wp_upload_dir();
		$this->path = $this->udir['basedir'].'/';
		$this->url  = $this->udir['baseurl'].'/';

		// load_plugin_textdomain( 'ImageCare' );

		add_action('admin_menu',               array( $this, 'add_admin_menu' ));
		add_action('admin_enqueue_scripts',    array( $this, 'admin_enqueues' ));

		add_action('wp_ajax_imagecare_maxres',     array( $this, 'do_maxres'     ));
		add_action('wp_ajax_imagecare_cleanregen', array( $this, 'do_cleanregen' ));
		add_action('wp_ajax_imagecare_regensizes', array( $this, 'do_regensizes' ));
	}

	public function add_admin_menu() {
		$this->menu_id = add_management_page( __('ImageCare', 'imagecare'), __('ImageCare', 'imagecare'), $this->capability, 'imagecare', array($this, 'iface') );
	}

	public function admin_enqueues( $hook_suffix ) {
		if($hook_suffix!=$this->menu_id)
			return;

		/* if(wp_script_is('jquery-ui-widget', 'registered'))
			wp_enqueue_script( 'jquery-ui-progressbar', plugins_url( 'jquery-ui/jquery.ui.progressbar.min.js', __FILE__ ), array( 'jquery-ui-core', 'jquery-ui-widget' ), '1.8.6' );
		else
			wp_enqueue_script( 'jquery-ui-progressbar', plugins_url( 'jquery-ui/jquery.ui.progressbar.min.1.7.2.js', __FILE__ ), array( 'jquery-ui-core' ), '1.7.2' );

		wp_enqueue_style( 'jquery-ui-imagecare', plugins_url( 'jquery-ui/redmond/jquery-ui-1.7.2.custom.css', __FILE__ ), array(), '1.7.2' ); */
	}

	public function iface() {
		include('inc-iface-common.php');
	}

	private function check_before_do($action) {
		if(!current_user_can($this->capability))
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		check_admin_referer('imagecare', $action);
		return true;
	}

	private function do_analyze($anal) {
		$this->anal = $anal;
		include('inc-func-analyze.php');
	}

	public function do_maxres() {
		include('inc-func-maxres.php');
	}

	public function do_cleanregen() {
		include('inc-func-cleanregen.php');
	}

	public function do_regensizes() {
		include('inc-func-regensizes.php');
	}

	public function do_backup() {
		include('inc-func-backup.php');
	}

	private function shallI($what) {
		return empty($this->anal) || in_aray($what, $this->anal);
	}

	/*
	 *
	 */

	private function get_image_sizes() {
		global $_wp_additional_image_sizes;

		$sizes = array();
		foreach(get_intermediate_image_sizes() as $_size) {
			if(in_array($_size, array('thumbnail', 'medium', 'medium_large', 'large'))) {
				$sizes[$_size]['std']    = true;
				$sizes[$_size]['width']  = get_option("{$_size}_size_w");
				$sizes[$_size]['height'] = get_option("{$_size}_size_h");
				$sizes[$_size]['crop']   = (bool) get_option("{$_size}_crop");
			} elseif(isset($_wp_additional_image_sizes[ $_size ])) {
				$sizes[$_size] = array(
					'std'    => false,
					'width'  => $_wp_additional_image_sizes[$_size]['width'],
					'height' => $_wp_additional_image_sizes[$_size]['height'],
					'crop'   => $_wp_additional_image_sizes[$_size]['crop'],
				);
			}
		}
		return $sizes;
	}



	private function get_db_sizes(&$files, &$amt_orgs, &$amt_total) {
		global $wpdb;
		$lmt_w = get_option('imagecare_lmt_w', 1024);
		$lmt_h = get_option('imagecare_lmt_h', 1024);
		$sizes_def = $this->get_image_sizes(); // TODO Evitar esta doble llamada
		$sizes_fnd = array();
		$sql  = "SELECT * FROM {$this->prefix}posts LEFT JOIN {$this->prefix}postmeta ON {$this->prefix}posts.ID={$this->prefix}postmeta.post_id WHERE post_mime_type='image/jpeg' AND meta_key='_wp_attachment_metadata'";
		$recs = $wpdb->get_results($sql, OBJECT);
		foreach($recs as $k=>$rec) {
			$amt_orgs++;
			$amt_total++;
			$info = unserialize($rec->meta_value);
			$dir  = dirname($info['file']).'/';
			if($info['width']>$lmt_w || $info['height']>$lmt_h) {
				$files['hr'][] = $info['file'];
			}
			foreach($info['sizes'] as $name=>$size) {
				$amt_total++;
				if(!isset($sizes_fnd[$name]))
					$sizes_fnd[$name] = array('ok'=>0, 'ko'=>0);
				if($this->checkSize($sizes_def, $size['width'], $size['height'])) {
					$files['ok'][] = $dir.$size['file'];
					$sizes_fnd[$name]['ok']++;
				} else {
					$files['ko'][] = $dir.$size['file']; // array($name, );
					$sizes_fnd[$name]['ko']++;
				}
			}
		}
		return $sizes_fnd;
	}



	private function get_disk_sizes(&$files, &$amt) {
		$lmt_w = get_option('imagecare_lmt_w', 1024);
		$lmt_h = get_option('imagecare_lmt_h', 1024);
		$sizes_def = $this->get_image_sizes(); // TODO Evitar esta doble llamada
		$sizes_fnd = array();
		$filex = $this->readfiles($this->path);
		foreach($filex as $file) {
			$amt++;
			$mtx = array();
			$dir = dirname($file).'/';
			if(preg_match('/-(\d+)x(\d+)\./', $file, $mtx)===1) {
				if($mtx[1]>$lmt_w || $mtx[2]>$lmt_h) {
					$files['hr'][] = $file;
				}
				if($name = $this->checkSize($sizes_def, $mtx[1], $mtx[2])) {
					if(!isset($sizes[$name]))
						$sizes[$name] = array('ok'=>0);
					$files['ok'][] = $file;
					$sizes_fnd[$name]['ok']++;
				} else {
					$name = $mtx[1].'×'.$mtx[2];
					if(!isset($sizes[$name]))
						$sizes[$name] = array('ko'=>array());
					$files['ko'][] = $file; // array($name, );
					$sizes_fnd[$name]['ko'][] = $file;
				}
			}
		}
		return $sizes_fnd;
	}



	private function readfiles($path, $stop=false) {
		$retval = array();
		$files  = glob($path.'*');
		foreach($files as $file) {
			if(is_dir($file)) {
				$temp   = $this->readfiles($file.'/', true);
				$retval = array_merge($retval, $temp);
			} else {
				$retval[] = str_replace($this->path, '', $file);
			}
		}
		return $retval;
	}



	private function checkSize($sizes, $w, $h) {
		$rv = false;
		foreach($sizes as $key=>$size) {
			if($size['crop']) {
				if( $w==$size['width'] || $h==$size['height'] )
					$rv = $key;
			} else {
				if(
					($w==$size['width'] && $h==$size['height']) ||
					($w==$size['width'] && $h<$size['height']) ||
					($w<$size['width'] && $h==$size['height']) ||
					($w==$size['width'] && 0==$size['height']) ||
					(0==$size['width'] && $h==$size['height'])
				)
					$rv = $key;
			}
		}
		return $rv;
	}

	public function safeYearMonth($elm) {
		return preg_match('/\d{4}-\d{2}/', $elm)===1 ? "'$elm'" : "''";
	}

	private function getImages() {
		global $wpdb;
		$sql  = "SELECT meta_value FROM {$this->prefix}posts p INNER JOIN {$this->prefix}postmeta pm ON p.ID=pm.post_id AND meta_key='_wp_attachment_metadata'";
		$recs = $wpdb->get_results($sql, OBJECT);
		return $recs;
	}

	private function getImageIDs() {
		global $wpdb;
		$sql = "SELECT GROUP_CONCAT(ID) as ids FROM {$this->prefix}posts WHERE post_mime_type like 'image/%'";
		$row = $wpdb->get_row($sql, OBJECT);
		return explode(chr(44), $row->ids);
	}

}

add_action( 'init', 'ImageCare' );

function ImageCare() {
	new ImageCare();
}

?>