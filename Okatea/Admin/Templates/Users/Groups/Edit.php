<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Users\Groups;

$view->extend('layout');

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_users'), $view->generateUrl('Users_index'));
$okt->page->addGlobalTitle(__('c_a_menu_users_groups'), $view->generateUrl('Users_groups'));
$okt->page->addGlobalTitle(__('c_a_users_edit_group'));


# button set
$okt->page->setButtonset('usersGroups', array(
	'id' => 'users-groups-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title'     => __('c_c_action_Go_back'),
			'url'       => $view->generateUrl('Users_groups'),
			'ui-icon'   => 'arrowreturnthick-1-w'
		),
		array(
			'permission' => true,
			'title'      => __('c_a_users_add_group'),
			'url'        => $view->generateUrl('Users_groups_add'),
			'ui-icon'    => 'plusthick'
		),
		array(
			'permission' => !in_array($iGroupId, Groups::$native),
			'title'      => __('c_c_action_Delete'),
			'url'        => $view->generateUrl('Users_groups').'?delete_id='.$iGroupId,
			'ui-icon'    => 'closethick',
			'onclick'    => 'return window.confirm(\''.$view->escapeJS(__('c_a_users_confirm_group_deletion')).'\')',
		)
	)
));

# Tabs
$okt->page->tabs();

?>

<?php echo $okt->page->getButtonSet('usersGroups'); ?>

<form action="<?php echo $view->generateUrl('Users_groups_edit', array('group_id' => $iGroupId)) ?>" method="post">
	<div id="tabered">
		<ul>
			<li><a href="#tab-definition"><span><?php _e('c_a_users_groups_definition') ?></span></a></li>
			<li><a href="#tab-permissions"><span><?php _e('c_a_users_groups_permissions') ?></span></a></li>
		</ul>

		<div id="tab-definition">
			<h3><?php _e('c_a_users_groups_definition') ?></h3>

			<p class="field"><label for="title" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_Title') ?></label>
			<?php echo form::text('title', 40, 255, $view->escape($title)) ?></p>
		</div><!-- #tab-definition -->

		<div id="tab-permissions">
			<h3><?php _e('c_a_users_groups_permissions') ?></h3>

			<?php if ($iGroupId == Groups::SUPERADMIN) : ?>
			<p><em><?php printf(__('c_a_users_groups_error_permissions_sudo'), $title, $iGroupId) ?></em></p>

			<?php elseif ($iGroupId == Groups::GUEST) : ?>
			<p><em><?php printf(__('c_a_users_groups_error_permissions_guest'), $title, $iGroupId) ?></em></p>

			<?php else : ?>

			<?php foreach($aPermissions as $group) :
				if (empty($group['perms'])) continue; ?>

				<?php if (!empty($group['libelle'])) : ?>
				<h4><?php echo $group['libelle'] ?></h4>
				<?php endif; ?>

				<ul class="checklist">
					<?php foreach ($group['perms'] as $perm => $libelle) : ?>
					<li><label for="perms_<?php echo $perm ?>"><?php
					echo form::checkbox(array('perms['.$perm.']', 'perms_'.$perm), 1, in_array($perm, $aPerms)) ?>
					<?php echo $libelle ?></label></li>
					<?php endforeach; ?>
				</ul>
			<?php endforeach; ?>
			<?php endif; ?>
		</div><!-- #tab-permissions -->

	</div><!-- #tabered -->

	<p><?php echo $okt->page->formtoken(); ?>
	<?php echo form::hidden('form_sent', 1)?>
	<input type="submit" value="<?php _e('c_c_action_Edit') ?>" /></p>
</form>
