<?php
/**
 * @ingroup okt_module_users
 * @brief Gestion des groupes d'utilisateurs
 *
 */

use Tao\Admin\Page;
use Tao\Forms\Statics\FormElements as form;
use Tao\Core\Authentification;

# Accès direct interdit
if (!defined('ON_MODULE')) die;

$do = !empty($_REQUEST['do']) ? $_REQUEST['do'] : null;
$group_id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : null;

$add_title = '';

$edit_title = '';

if ($group_id) {
	$group = $okt->users->getGroup($group_id);
	$edit_title = $group->title;
}


/* Traitements
----------------------------------------------------------*/

# ajout d’un groupe
if (!empty($_POST['add_group']))
{
	$add_title = !empty($_POST['add_title']) ? $_POST['add_title'] : '';

	if (empty($add_title)) {
		$okt->error->set(__('m_users_must_enter_group_title'));
	}

	if ($okt->error->isEmpty())
	{
		$okt->users->addGroup($add_title);

		$okt->page->flashMessages->addSuccess(__('m_users_group_added'));

		http::redirect('module.php?m=users&action=groups');
	}
}

# modification d’un groupe
if (!empty($_POST['edit_group']))
{
	$edit_title = !empty($_POST['edit_title']) ? $_POST['edit_title'] : '';

	if (empty($edit_title)) {
		$okt->error->set(__('m_users_must_enter_group_title'));
	}

	if ($okt->error->isEmpty())
	{
		$okt->users->updGroup($group_id, $edit_title);

		$okt->page->flashMessages->addSuccess(__('m_users_group_edited'));

		http::redirect('module.php?m=users&action=groups');
	}
}

# suppression d'un groupe
if ($group_id && $do == 'delete')
{
	if (in_array($group_id, array(Authentification::superadmin_group_id,Authentification::admin_group_id,Authentification::guest_group_id,Authentification::member_group_id))) {
		$okt->error->set(__('m_users_cannot_remove_group'));
	}
	else
	{
		$okt->users->deleteGroup($group_id);

		$okt->page->flashMessages->addSuccess(__('m_users_group_deleted'));

		http::redirect('module.php?m=users&action=groups');
	}
}


/* Affichage
----------------------------------------------------------*/

# Liste des groupes
$groups = $okt->users->getGroups();

# Titre de la page
$okt->page->addGlobalTitle('Groupes');

# Tabs
$okt->page->tabs();


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<div id="tabered">
	<ul>
		<?php if ($group_id) : ?>
		<li><a href="#tab-edit"><span><?php _e('m_users_edit_group')?></span></a></li>
		<?php endif; ?>
		<li><a href="#tab-list"><span><?php _e('m_users_group_list')?></span></a></li>
		<li><a href="#tab-add"><span><?php _e('m_users_add_group')?></span></a></li>
	</ul>

	<?php if ($group_id) : ?>
	<div id="tab-edit">
		<form action="module.php" method="post">
			<h3><?php _e('m_users_edit_group')?></h3>

			<p class="field"><label for="edit_title"><?php _e('c_c_Title')?></label>
			<?php echo form::text('edit_title',40,255,html::escapeHTML($edit_title)) ?></p>

			<p><?php echo form::hidden('action','groups') ?>
			<?php echo form::hidden('m','users') ?>
			<?php echo form::hidden('id',$group_id) ?>
			<?php echo form::hidden('edit_group',1) ?>
			<?php echo Page::formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_Edit') ?>" /></p>
		</form>
	</div><!-- #tab-edit -->
	<?php endif; ?>

	<div id="tab-list">
		<h3><?php _e('m_users_group_list') ?></h3>
		<table class="common">
			<caption><?php _e('m_users_group_list') ?></caption>
			<thead><tr>
				<th scope="col"><?php _e('c_c_Name')?></th>
				<th scope="col"><?php _e('c_c_Actions')?></th>
			</tr></thead>
			<tbody>
			<?php $count_line = 0;
			while ($groups->fetch()) :

				if ($groups->group_id == Authentification::superadmin_group_id) {
					continue;
				}

				$td_class = $count_line%2 == 0 ? 'even' : 'odd';
				$count_line++;
			?>
			<tr>
				<th scope="row" class="<?php echo $td_class ?> fake-td"><?php echo html::escapeHTML($groups->title) ?></th>
				<td class="<?php echo $td_class ?> small">
					<ul class="actions">
						<li><a href="module.php?m=users&amp;action=groups&amp;do=edit&amp;id=<?php echo $groups->group_id ?>"
						title="<?php _e('c_c_action_Edit') ?> <?php echo html::escapeHTML($groups->title) ?>"
						class="icon pencil"><?php _e('c_c_action_Edit')?></a></li>

					<?php if (in_array($groups->group_id,array(Authentification::superadmin_group_id,Authentification::admin_group_id,Authentification::guest_group_id,Authentification::member_group_id))) : ?>
						<li class="disabled"><span class="icon delete"></span><?php _e('c_c_action_Delete')?></li>
					<?php else : ?>
						<li><a href="module.php?m=users&amp;action=groups&amp;do=delete&amp;id=<?php echo $groups->group_id ?>"
						onclick="return window.confirm('<?php echo html::escapeJS(__('m_users_confirm_group_deletion')) ?>')"
						title="<?php _e('c_c_action_Delete')?> <?php echo html::escapeHTML($groups->title) ?>"
						class="icon delete"><?php _e('c_c_action_Delete')?></a><li>
					<?php endif; ?>
					</ul>
				</td>
			</tr>
			<?php endwhile; ?>
			</tbody>
		</table>
	</div><!-- #tab-list -->

	<div id="tab-add">
		<h3><?php _e('m_users_add_group')?></h3>
		<form action="module.php" method="post">

			<p class="field"><label for="add_title"><?php _e('c_c_Title')?></label>
			<?php echo form::text('add_title',40,255,html::escapeHTML($add_title)) ?></p>

			<p><?php echo form::hidden('action','groups') ?>
			<?php echo form::hidden('m','users') ?>
			<?php echo form::hidden('add_group',1) ?>
			<?php echo Page::formtoken(); ?>
			<input type="submit" value="<?php _e('c_c_action_Add') ?>" /></p>
		</form>
	</div><!-- #tab-add -->

</div><!-- #tabered -->

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
