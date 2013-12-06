<?php
/**
 * @ingroup okt_module_users
 * @brief Liste des utilisateurs
 *
 */

use Okatea\Core\Authentification;

# Accès direct interdit
if (!defined('ON_USERS_MODULE')) die;


/* json posts list for autocomplete
----------------------------------------------------------*/

if (!empty($_REQUEST['json']) && !empty($_GET['term']))
{
	$aParams = array();
	$aParams['group_id_not'][] = Authentification::guest_group_id;

	if (!$okt->user->is_superadmin) {
		$aParams['group_id_not'][] = Authentification::superadmin_group_id;
	}

	if (!$okt->user->is_admin) {
		$aParams['group_id_not'][] = Authentification::admin_group_id;
	}

	$aParams['search'] = $_GET['term'];

	$rsUsers = $okt->users->getUsers($aParams);

	$aResults = array();
	while ($rsUsers->fetch())
	{
		$aResults[] = $rsUsers->username;
		$aResults[] = $rsUsers->email;
		if (!empty($rsUsers->firstname)) {
			$aResults[] = $rsUsers->firstname;
		}
		if (!empty($rsUsers->lastname)) {
			$aResults[] = $rsUsers->lastname;
		}
	}

	header('Content-type: application/json');
	echo json_encode($aResults);

	exit;
}


/* Initialisations
----------------------------------------------------------*/

# initialisation des filtres
$filters = new usersFilters($okt->users, 'admin');


/* Traitements
----------------------------------------------------------*/

# Enable user status
if (!empty($_GET['enable']))
{
	if ($okt->users->setUserStatus($_GET['enable'], 1))
	{
		# log admin
		$okt->logAdmin->info(array(
			'code' => 30,
			'component' => 'users',
			'message' => 'user #'.$_GET['enable']
		));

		http::redirect('module.php?m=users&action=index&switched=1');
	}
}

# Disable user status
if (!empty($_GET['disable']))
{
	if ($okt->users->setUserStatus($_GET['disable'], 0))
	{
		# log admin
		$okt->logAdmin->info(array(
			'code' => 31,
			'component' => 'users',
			'message' => 'user #'.$_GET['disable']
		));

		http::redirect('module.php?m=users&action=index&switched=1');
	}
}

# Supprimer utilisateur
if (!empty($_GET['delete']) && $okt->checkPerm('users_delete'))
{
	if ($okt->users->deleteUser($_GET['delete']))
	{
		# log admin
		$okt->logAdmin->warning(array(
			'code' => 42,
			'component' => 'users',
			'message' => 'user #'.$_GET['delete']
		));

		# -- CORE TRIGGER : adminModUsersDeleteProcess
		$okt->triggers->callTrigger('adminModUsersDeleteProcess', $okt, $_GET['delete']);

		$okt->page->flashMessages->addSuccess(__('m_users_user_deleted'));

		http::redirect('module.php?m=users&action=index');
	}
}

# Ré-initialisation filtres
if (!empty($_GET['init_filters']))
{
	$filters->initFilters();
	http::redirect('module.php?m=users&action=index');
}


/* Affichage
----------------------------------------------------------*/

# initialisation des filtres
$aParams = array();
$aParams['group_id_not'][] = Authentification::guest_group_id;

if (!$okt->user->is_superadmin) {
	$aParams['group_id_not'][] = Authentification::superadmin_group_id;
}

if (!$okt->user->is_admin) {
	$aParams['group_id_not'][] = Authentification::admin_group_id;
}

$sSearch = null;

if (!empty($_REQUEST['search']))
{
	$sSearch = trim($_REQUEST['search']);
	$aParams['search'] = $sSearch;
}

$filters->setUsersParams($aParams);


# création des filtres
$filters->getFilters();

# initialisation de la pagination
$num_filtered_users = $okt->users->getUsers($aParams,true);

$pager = new adminPager($filters->params->page, $num_filtered_users, $filters->params->nb_per_page);

$num_pages = $pager->getNbPages();

$filters->normalizePage($num_pages);

$aParams['limit'] = (($filters->params->page-1)*$filters->params->nb_per_page).','.$filters->params->nb_per_page;


# liste des utilisateurs
$rsUsers = $okt->users->getUsers($aParams);


# Autocomplétion du formulaire de recherche
$okt->page->js->addReady('
	$("#search").autocomplete({
		source: "module.php?search=&m=users&action=index&json=1",
		minLength: 2
	});
');

if (!empty($sSearch))
{
	$okt->page->js->addFile(OKT_PUBLIC_URL.'/js/jquery/putCursorAtEnd/jquery.putCursorAtEnd.min.js');
	$okt->page->js->addReady('
		$("#search").putCursorAtEnd();
	');
}

# ajout de boutons
$okt->page->addButton('users',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_display_filters'),
	'url' 			=> '#',
	'ui-icon' 		=> 'search',
	'active' 		=> $filters->params->show_filters,
	'id'			=> 'filter-control',
	'class'			=> 'button-toggleable'
));


# Filters control
if ($okt->users->config->admin_filters_style == 'slide')
{
	# Slide down
	$okt->page->filterControl($filters->params->show_filters);
}
elseif ($okt->users->config->admin_filters_style == 'dialog')
{
	# Display a UI dialog box
	$okt->page->js->addReady("
		$('#filters-form').dialog({
			title:'".html::escapeJS(__('m_users_users_display_filters'))."',
			autoOpen: false,
			modal: true,
			width: 500,
			height: 300
		});

		$('#filter-control').click(function() {
			$('#filters-form').dialog('open');
		})
	");
}

# nombre d'utilisateur en attente de validation
$num_waiting_validation = $okt->users->getUsers(array('group_id'=>Authentification::unverified_group_id), true);

if ($num_waiting_validation == 1) {
	$okt->page->flashMessages->addWarning(__('m_users_one_user_in_wait_of_validation'));
}
elseif ($num_waiting_validation > 1) {
	$okt->page->flashMessages->addWarning(sprintf(__('m_users_%s_users_in_wait_of_validation'), $num_waiting_validation));
}


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<div class="double-buttonset">
	<div class="buttonsetA">
		<?php echo $okt->page->getButtonSet('users'); ?>
	</div>
	<div class="buttonsetB">
		<form action="module.php" method="get" id="search_form" class="search_form">
			<p><label for="search"><?php _e('m_users_list_Search') ?></label>
			<?php echo form::text('search',20,255,html::escapeHTML((isset($sSearch) ? $sSearch : ''))); ?>

			<?php echo form::hidden('m','users') ?>
			<?php echo form::hidden('action','index') ?>
			<input type="submit" name="search_submit" id="search_submit" value="ok" /></p>
		</form>
	</div>
</div>

<?php # formulaire des filtres ?>
<form action="module.php" method="get" id="filters-form">
	<fieldset>
		<legend><?php _e('m_users_users_display_filters')?></legend>

		<?php echo $filters->getFiltersFields('<div class="three-cols">%s</div>'); ?>

		<p><?php echo form::hidden('m','users') ?>
		<?php echo form::hidden('action','index') ?>
		<input type="submit" name="<?php echo $filters->getFilterSubmitName() ?>" value="<?php _e('c_c_action_Display') ?>" />
		<a href="module.php?m=users&amp;action=index&amp;init_filters=1"><?php _e('c_c_reset_filters')?></a></p>
	</fieldset>
</form>

<?php if ($rsUsers->isEmpty()) : ?>

	<?php if (!empty($sSearch)) : ?>
	<p><?php _e('m_users_no_searched_user') ?></p>

	<?php elseif ($filters->params->show_filters) : ?>
	<p><?php _e('m_users_no_filtered_user') ?></p>

	<?php else : ?>
	<p><?php _e('m_users_no_user') ?></p>

	<?php endif; ?>

<?php endif; ?>

<?php if (!$rsUsers->isEmpty()) : ?>
<table class="common">
	<caption><?php _e('m_users_users_list')?></caption>
	<thead><tr>
		<th scope="col"><?php _e('c_c_user_Username')?></th>
		<th scope="col"><?php _e('c_c_Email')?></th>
		<th scope="col"><?php _e('c_c_Group')?></th>
		<th scope="col"><?php _e('m_users_last_connection')?></th>
		<th scope="col"><?php _e('m_users_registration_date')?></th>
		<th scope="col" class="small"><?php _e('c_c_Actions')?></th>
	</tr></thead>
	<tbody>
	<?php $iCountLine = 0;
	while ($rsUsers->fetch()) :

		$sTdClass = $iCountLine%2 == 0 ? 'even' : 'odd';
		$iCountLine++;

		if (!$rsUsers->active) {
			$sTdClass .= ' disabled';
		}
	?>
	<tr>
		<th class="<?php echo $sTdClass ?> fake-td">
			<h3 class="title"><a href="module.php?m=users&amp;action=edit&amp;id=<?php echo $rsUsers->id ?>"><?php echo html::escapeHTML($rsUsers->username) ?></a></h3>
			<p><?php echo html::escapeHTML($rsUsers->firstname.' '.$rsUsers->lastname) ?></p>
		</th>
		<td class="<?php echo $sTdClass ?>"><a href="mailto:<?php echo $rsUsers->email ?>"><?php echo $rsUsers->email ?></a></td>
		<td class="<?php echo $sTdClass ?>"><?php

		if ($rsUsers->group_id == Authentification::unverified_group_id) {
			_e('m_users_wait_of_validation');
		}
		elseif (!empty($rsUsers->title)) {
			echo html::escapeHTML($rsUsers->title);
		}

		?></td>
		<td class="<?php echo $sTdClass ?>"><?php echo dt::str('%A %d %B %Y %H:%M', $rsUsers->last_visit) ?></td>
		<td class="<?php echo $sTdClass ?>"><?php echo dt::str('%A %d %B %Y %H:%M', $rsUsers->registered) ?></td>
		<td class="<?php echo $sTdClass ?> nowrap">
			<ul class="actions">

				<li>
				<?php if ($rsUsers->group_id == Authentification::unverified_group_id && $okt->checkPerm('users_edit')) : ?>
					<a href="module.php?m=users&amp;action=edit&amp;id=<?php echo $rsUsers->id ?>&amp;valide=1"
					title="<?php _e('m_users_validate_the_user')?> <?php echo html::escapeHTML($rsUsers->username) ?>"
					class="icon time"><?php _e('m_users_validate_the_user')?></a>
					<?php else : ?>
					<span class="icon user"></span><?php _e('m_users_validated_user')?>
				<?php endif; ?>
				</li>

				<li>
				<?php if ($rsUsers->active) : ?>
				<a href="module.php?m=users&amp;action=index&amp;disable=<?php echo $rsUsers->id ?>"
				class="icon tick"><?php _e('c_c_status_Active')?></a>
				<?php else : ?>
				<a href="module.php?m=users&amp;action=index&amp;enable=<?php echo $rsUsers->id ?>"
				class="icon cross"><?php _e('c_c_status_Inactive')?></a>
				<?php endif; ?>
				</li>

				<?php if ($okt->checkPerm('users_edit')) : ?>
				<li><a href="module.php?m=users&amp;action=edit&amp;id=<?php echo $rsUsers->id ?>"
				title="<?php _e('m_users_edit_the_user')?> <?php echo html::escapeHTML($rsUsers->username) ?>"
				class="icon pencil"><?php _e('c_c_action_Edit')?></a></li>
				<?php endif; ?>

				<?php if ($okt->checkPerm('users_delete')) : ?>
				<li><a href="module.php?m=users&amp;action=index&amp;delete=<?php echo $rsUsers->id ?>"
				onclick="return window.confirm('<?php echo html::escapeJS(__('m_users_confirm_user_deletion')) ?>')"
				title="<?php _e('m_users_delete_the_user')?> <?php echo html::escapeHTML($rsUsers->username) ?>"
				class="icon delete"><?php _e('c_c_action_Delete')?></a></li>
				<?php endif; ?>

			</ul>
		</td>
	</tr>
	<?php endwhile; ?>
	</tbody>
</table>

<?php if ($num_pages > 1) : ?>
<ul class="pagination"><?php echo $pager->getLinks(); ?></ul>
<?php endif; ?>

<?php endif; ?>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
