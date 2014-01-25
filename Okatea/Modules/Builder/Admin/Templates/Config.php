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

<form action="<?php echo $view->generateUrl('Builder_config'); ?>" method="post">
	<div id="tabered">
		<ul>
			<li></li>
			<li></li>
			<li></li>
		</ul>

	</div><!-- #tabered -->

	<p><?php echo form::hidden(array('form_sent'), 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>
