<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$view->extend('layout');

# module title tag
$okt->page->addGlobalTitle(__('m_builder_menu'));

# tabs
$okt->page->tabs();

?>

<div id="tabered">
	<ul>
		<li><a href="#tab_builder"><?php _e('m_builder_menu') ?></a></li>
		<li><a href="#tab_config"><?php _e('m_builder_menu_config') ?></a></li>
	</ul>

	<div id="tab_builder">
	</div><!-- #tab_builder -->

	<div id="tab_config">
		<form action="<?php echo $view->generateUrl('Builder'); ?>" method="post">


			<p><?php echo form::hidden('config_sent', 1); ?>
			<?php echo $okt->page->formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
		</form>
	</div><!-- #tab_config -->
</div><!-- #tabered -->
