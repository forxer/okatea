<?php

$view->extend('layout');

$okt->page->addGlobalTitle(sprintf(__('c_a_modules_update_module_%s'), $oInstallModule->name()));

?>

<?php echo $oInstallModule->checklist->getHTML(); ?>

<div class="checklistlegend">
	<p><?php _e('c_c_checklist_legend') ?></p>
	<?php echo $oInstallModule->checklist->getLegend(); ?>
</div>

<p class="ui-helper-clearfix"><a class="button" href="<?php echo $sNextUrl ?>"><?php _e('Continue') ?></a></p>
