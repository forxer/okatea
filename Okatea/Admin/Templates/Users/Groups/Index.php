<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Okatea\Tao\Users\Groups;

$view->extend('Layout');

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_users'), $view->generateAdminUrl('Users_index'));

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_users_groups'));

# button set
$okt->page->setButtonset('usersGroups', array(
	'id' => 'users-groups-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('c_a_users_add_group'),
			'url' => $view->generateAdminUrl('Users_groups_add'),
			'ui-icon' => 'plusthick'
		)
	)
));

?>

<?php echo $okt->page->getButtonSet('usersGroups'); ?>

<table class="common">
	<caption><?php _e('c_a_users_group_list') ?></caption>
	<thead>
		<tr>
			<th scope="col"><?php _e('c_c_Name') ?></th>
			<th scope="col"><?php _e('c_c_Description') ?></th>
			<th scope="col" class="small nowrap"><?php _e('c_a_users_group_num_users') ?></th>
			<th scope="col" class="small nowrap"><?php _e('c_c_Actions') ?></th>
		</tr>
	</thead>
	<tbody>
	<?php

	$count_line = 0;
	foreach ($aGroups as $aGroup) :

		$td_class = $count_line % 2 == 0 ? 'even' : 'odd';
		$count_line ++;
		?>
	<tr>
			<th scope="row" class="<?php echo $td_class ?> fake-td">
				<p class="title">
					<a
						href="<?php echo $view->generateAdminUrl('Users_groups_edit', array('group_id' => $aGroup['group_id'])) ?>">
			<?php echo $view->escape($aGroup['title']) ?></a>
				</p>
			</th>
			<td class="<?php echo $td_class ?>" style="min-width: 40%">
				<p class="note"><?php echo $view->escape($aGroup['description']) ?></p>
			</td>
			<td class="<?php echo $td_class ?> small nowrap">
			<?php if ($aGroup['group_id'] != Groups::GUEST) : ?>
			<p>
					<a
						href="<?php echo $view->generateAdminUrl('Users_index') ?>?group_id=<?php echo $aGroup['group_id'] ?>"
						title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_a_users_group_%s_show_users'), $aGroup['title'])); ?>">
			<?php
			if ($aGroup['num_users'] <= 0)
			{
				_e('c_a_users_group_no_user');
			}
			elseif ($aGroup['num_users'] == 1)
			{
				_e('c_a_users_group_one_user');
			}
			else
			{
				printf(__('c_a_users_group_%s_users'), $aGroup['num_users']);
			}
			?></a>
				</p>
			<?php endif; ?>
		</td>
			<td class="<?php echo $td_class ?> small nowrap">
				<ul class="actions">
					<li><a
						href="<?php echo $view->generateAdminUrl('Users_groups_edit', array('group_id' => $aGroup['group_id'])) ?>"
						title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_a_users_edit_the_group_%s'), $aGroup['title'])) ?>"
						class="icon pencil"><?php _e('c_c_action_Edit')?></a></li>

			<?php if (in_array($aGroup['group_id'], Groups::$native) || $aGroup['num_users'] > 0) : ?>
				<li class="disabled nowrap"
						title="<?php _e('c_c_users_error_cannot_remove_group') ?>"><span
						class="icon delete"></span><?php _e('c_c_action_Delete')?></li>
			<?php else : ?>
				<li><a
						href="<?php echo $view->generateAdminUrl('Users_groups') ?>?delete_id=<?php echo $aGroup['group_id'] ?>"
						onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_users_confirm_group_deletion')) ?>')"
						title="<?php echo $view->escapeHtmlAttr(sprintf(__('c_a_users_delete_the_group_%s'), $aGroup['title'])) ?>"
						class="icon delete"><?php _e('c_c_action_Delete') ?></a>

					<li>
			<?php endif; ?>








				</ul>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
