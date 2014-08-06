<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$view->extend('Layout');

$okt->page->addGlobalTitle(__('c_a_modules_management'), $view->generateAdminUrl('config_modules'));
$okt->page->addGlobalTitle(sprintf(__('c_a_modules_reinstall_module_%s'), $oInstallModule->name()));

?>

<?php echo $oInstallModule->checklist->getHTML(); ?>

<div class="checklistlegend">
	<p><?php _e('c_c_checklist_legend') ?></p>
	<?php echo $oInstallModule->checklist->getLegend(); ?>
</div>

<p class="ui-helper-clearfix">
	<a class="button"
		href="<?php echo $view->generateAdminUrl('config_modules') ?>"><?php _e('Continue') ?></a>
</p>
