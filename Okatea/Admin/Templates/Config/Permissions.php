<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('Layout');

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_config_permissions'));

$iNumGroup = count($aGroups);

if ($iNumGroup > 1)
{
	$okt->page->tabs();
}
?>

<form action="<?php echo $view->generateUrl('config_permissions') ?>"
	method="post">
	<div id="tabered">
	<?php if ($iNumGroup > 1) : ?>
	<ul>
		<?php foreach ($aGroups as $group_id=>$group_title) : ?>
		<li><a href="#tab-group-<?php echo $group_id ?>"><span><?php echo $view->escape($group_title) ?></span></a></li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>

	<?php foreach ($aGroups as $group_id=>$group_title) : ?>
	<div id="tab-group-<?php echo $group_id ?>">
			<h3><?php printf(__('c_a_config_permissions_group_%s'), $view->escape($group_title)) ?></h3>

		<?php
		
		foreach ($aPermissions as $group)
		:
			if (empty($group['perms']))
				continue;
			?>

			<?php if (!empty($group['libelle'])) : ?>
			<h4><?php echo $group['libelle'] ?></h4>
			<?php endif; ?>

			<ul class="checklist">
				<?php foreach ($group['perms'] as $perm=>$libelle) : ?>
				<li><label for="perms_<?php echo $group_id ?>_<?php echo $perm ?>"><?php
				echo form::checkbox(array(
					'perms[' . $group_id . '][' . $perm . ']',
					'perms_' . $group_id . '_' . $perm
				), 1, in_array($perm, $aPerms[$group_id]))?>
				<?php echo $libelle ?></label></li>
				<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>
	</div>
	<?php endforeach; ?>

</div>
	<!-- #tabered -->

	<p><?php echo form::hidden('sended_form',1)?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_edit') ?>" />
	</p>
</form>

