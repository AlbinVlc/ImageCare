<div id="message" class="updated fade" style="display:none"></div>
<div class="wrap regenthumbs">
	<h2><?php _e('ImageCare', 'imagecare'); ?></h2>
	<p><?php _e('A tool to manage images so they don´t waste disk space.', 'imagecare'); ?></p>
	<?php
		$page = $_GET['page'];
		$ctab = !empty($_GET['tab']) ? $_GET['tab'] : 'ini';
		$tabs = array( 'ini' => 'What I do' );
	//	if($ctab=='maxres')     $tabs[$ctab] = 'Limit max. size';
	//	if($ctab=='cleanregen') $tabs[$ctab] = 'Clean & Regen.';
		$tabs['maxres']     = 'Top resolution';
		$tabs['regensizes'] = 'Regenerate';
		$tabs['cleanregen'] = 'Clean & Regen.';
		$tabs['bup']        = 'Backup';
		echo '<div id="icon-themes" class="icon32"><br></div>';
		echo '<h2 class="nav-tab-wrapper">';
		foreach($tabs as $tab=>$name) {
			$class = ($tab==$ctab ? ' nav-tab-active' : '');
			echo '<a class="nav-tab'.$class.'" href="?page='.$page.'&tab='.$tab.'">'.$name.'</a>';
		}
		echo '</h2>';
		include('inc-iface-'.$ctab.'.php');
	?>
</div>