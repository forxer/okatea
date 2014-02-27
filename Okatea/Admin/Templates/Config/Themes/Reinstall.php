<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$view->extend('layout');

$okt->page->addGlobalTitle(__('c_a_themes_management'), $view->generateUrl('config_themes'));
$okt->page->addGlobalTitle(sprintf(__('c_a_themes_reinstall_theme_%s'), $oInstallTheme->name()));

?>

<?php echo $oInstallTheme->checklist->getHTML(); ?>

<div class="checklistlegend">
	<p><?php _e('c_c_checklist_legend') ?></p>
	<?php echo $oInstallTheme->checklist->getLegend(); ?>
</div>

<p class="ui-helper-clearfix"><a class="button" href="<?php echo $view->generateUrl('config_themes') ?>"><?php _e('Continue') ?></a></p>
