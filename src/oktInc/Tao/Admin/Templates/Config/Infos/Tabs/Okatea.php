<?php

$okt->page->openLinkInDialog('#changelog_link',array(
	'title' => 'CHANGELOG',
	'width' => 730,
	'height' => 500
));

?>

<h3><?php _e('c_a_infos_okatea_title') ?></h3>

<p>Okatea
	<?php if ($aOkateaInfos['version']) { echo ' '.__('c_a_infos_okatea_version').' <strong>'.$aOkateaInfos['version'].'</strong> '; } ?>
	[<a href="<?php echo $view->generateUrl('config_infos') ?>?show_changelog=1" id="changelog_link">changelog</a>]
</p>

<h4><?php _e('c_a_infos_okatea_prerequisites') ?></h4>
<?php foreach ($aOkateaInfos['requirements'] as $i => $group) : ?>

	<h5><?php echo $group['group_title'] ?></h5>
	<?php echo $aOkateaInfos['requirements'][$i]['check_'.$group['group_id']]->getHTML(); ?>

<?php endforeach; ?>

<?php if (!$aOkateaInfos['pass_test']) : ?>
<p><?php _e('c_a_infos_okatea_big_loose') ?></p>
<?php else : ?>
	<?php if (!$aOkateaInfos['warning_empty']) : ?>
	<p><?php _e('c_a_infos_okatea_warning') ?></p>
	<?php endif; ?>
<?php endif; ?>
