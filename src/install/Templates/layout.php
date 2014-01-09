<!DOCTYPE html>
<html class="" lang="<?php echo $okt->session->get('okt_install_language') ?>">
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="ROBOTS" content="NOARCHIVE,NOINDEX,NOFOLLOW" />

	<title><?php _e('i_'.$okt->session->get('okt_install_process_type').'_interface') ?> - Okatea <?php if ($okt->version) { echo $okt->version; } ?></title>

	<?php echo $okt->page->css ?>
</head>

<body>
<div id="page">
	<header>
		<div id="banner" class="ui-widget-header ui-corner-all">
			<h1>Okatea <span class="version"><?php if ($okt->version) { echo $okt->version; } ?></span></h1>
			<p id="desc"><?php _e('i_'.$okt->session->get('okt_install_process_type').'_interface') ?></p>
		</div><!-- #banner -->

		<?php echo $okt->stepper->display() ?>
	</header>
	<div id="main" class="ui-widget">

		<h2 id="page-title" class="ui-widget-header ui-corner-top"><?php if (!empty($title)) { echo $title; } else { _e('i_'.$okt->session->get('okt_install_process_type').'_interface'); }?></h2>

		<section id="content" class="ui-widget-content ui-corner-bottom">

			<?php if (!$okt->error->isEmpty()) : ?>
			<div class="errors_box">
				<h3><?php _e('i_errors') ?></h3>
				<?php echo $okt->error->get(); ?>
			</div>
			<?php endif; ?>

			<?php $view['slots']->output('_content'); ?>

		</section><!-- #content -->

		<footer>
			<p id="footer" class="clearb ui-widget ui-corner-all ui-state-default">
			Okatea <?php if ($okt->version) { echo ' version <strong>'.$okt->version.'</strong> '; } ?>
			</p><!-- #footer -->
		</footer>
	</div><!-- #main -->
</div><!-- #page -->

<?php echo $okt->page->js ?>

</body>
</html>