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

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_users_groups'));

# Tabs
$okt->page->tabs();

?>

<div id="tabered">
	<ul>
		<?php if ($iGroupId) : ?>
		<li><a href="#tab-edit"><span><?php _e('c_a_users_edit_group')?></span></a></li>
		<?php endif; ?>
		<li><a href="#tab-list"><span><?php _e('c_a_users_group_list')?></span></a></li>
		<li><a href="#tab-add"><span><?php _e('c_a_users_add_group')?></span></a></li>
	</ul>

	<?php if ($iGroupId) : ?>
	<div id="tab-edit">
		<form action="<?php echo $view->generateUrl('Users_groups') ?>?group_id=<?php echo $iGroupId ?>" method="post">
			<h3><?php _e('c_a_users_edit_group') ?></h3>

			<p class="field"><label for="edit_title"><?php _e('c_c_Title') ?></label>
			<?php echo form::text('edit_title', 40, 255, $view->escape($edit_title)) ?></p>

			<p><?php echo $okt->page->formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_Edit') ?>" /></p>
		</form>
	</div><!-- #tab-edit -->
	<?php endif; ?>

	<div id="tab-list">
		<h3><?php _e('c_a_users_group_list') ?></h3>
		<table class="common">
			<caption><?php _e('c_a_users_group_list') ?></caption>
			<thead><tr>
				<th scope="col"><?php _e('c_c_Name') ?></th>
				<th scope="col"><?php _e('c_c_Actions') ?></th>
			</tr></thead>
			<tbody>
			<?php $count_line = 0;
			while ($rsGroups->fetch()) :

				if ($rsGroups->group_id == Groups::SUPERADMIN) {
					continue;
				}

				$td_class = $count_line%2 == 0 ? 'even' : 'odd';
				$count_line++;
			?>
			<tr>
				<th scope="row" class="<?php echo $td_class ?> fake-td"><?php echo $view->escape($rsGroups->title) ?></th>
				<td class="<?php echo $td_class ?> small">
					<ul class="actions">
						<li><a href="<?php echo $view->generateUrl('Users_groups') ?>?group_id=<?php echo $rsGroups->group_id ?>"
						title="<?php _e('c_c_action_Edit') ?> <?php echo $view->escape($rsGroups->title) ?>"
						class="icon pencil"><?php _e('c_c_action_Edit')?></a></li>

					<?php if (in_array($rsGroups->group_id, array(Groups::ADMIN, Groups::ADMIN, Groups::MEMBER, Groups::GUEST))) : ?>
						<li class="disabled nowrap"><span class="icon delete"></span><?php _e('c_c_action_Delete')?></li>
					<?php else : ?>
						<li><a href="<?php echo $view->generateUrl('Users_groups') ?>?delete_id=<?php echo $rsGroups->group_id ?>"
						onclick="return window.confirm('<?php echo $view->escapeJs(__('c_a_users_confirm_group_deletion')) ?>')"
						title="<?php _e('c_c_action_Delete') ?> <?php echo $view->escape($rsGroups->title) ?>"
						class="icon delete"><?php _e('c_c_action_Delete') ?></a><li>
					<?php endif; ?>
					</ul>
				</td>
			</tr>
			<?php endwhile; ?>
			</tbody>
		</table>
	</div><!-- #tab-list -->

	<div id="tab-add">
		<h3><?php _e('c_a_users_add_group') ?></h3>
		<form action="<?php echo $view->generateUrl('Users_groups') ?>" method="post">

			<p class="field"><label for="add_title"><?php _e('c_c_Title') ?></label>
			<?php echo form::text('add_title', 40, 255, $view->escape($add_title)) ?></p>

			<p><?php echo $okt->page->formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_Add') ?>" /></p>
		</form>
	</div><!-- #tab-add -->

</div><!-- #tabered -->
