<?php

use Okatea\Tao\Authentification;
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

# titre de la page
$okt->page->addGlobalTitle(__('Users'), $view->generateUrl('Users_index'));

# button set
$okt->page->setButtonset('users', array(
	'id' => 'users-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => ($okt->page->action === 'add' || $okt->page->action === 'edit'),
			'title' => __('c_c_action_Go_back'),
			'url' => 'module.php?m=users&amp;action=index',
			'ui-icon' => 'arrowreturnthick-1-w'
		),
		array(
			'permission' => true,
			'title' => __('m_users_Add_user'),
			'url' => 'module.php?m=users&amp;action=add',
			'ui-icon' => 'plusthick'
		)
	)
));


# Autocomplétion du formulaire de recherche
$okt->page->js->addReady('
	$("#search").autocomplete({
		source: "'.$view->generateUrl('Users_index').'?json=1",
		minLength: 2
	});
');

if (!empty($sSearch))
{
	$okt->page->js->addFile($okt->options->public_url.'/plugins/putCursorAtEnd/jquery.putCursorAtEnd.min.js');
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
	'active' 		=> $okt->Users->filters->params->show_filters,
	'id'			=> 'filter-control',
	'class'			=> 'button-toggleable'
));


# Filters control
if ($okt->Users->config->admin_filters_style == 'slide')
{
	# Slide down
	$okt->page->filterControl($okt->Users->filters->params->show_filters);
}
elseif ($okt->Users->config->admin_filters_style == 'dialog')
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

?>


<div class="double-buttonset">
	<div class="buttonsetA">
		<?php echo $okt->page->getButtonSet('users'); ?>
	</div>
	<div class="buttonsetB">
		<form action="<?php echo $view->generateUrl('Users_index') ?>" method="get" id="search_form" class="search_form">
			<p><label for="search"><?php _e('m_users_list_Search') ?></label>
			<?php echo form::text('search', 20, 255, html::escapeHTML($sSearch)); ?>

			<input type="submit" name="search_submit" id="search_submit" value="ok" /></p>
		</form>
	</div>
</div>

<?php # formulaire des filtres ?>
<form action="<?php echo $view->generateUrl('Users_index') ?>" method="get" id="filters-form">
	<fieldset>
		<legend><?php _e('m_users_users_display_filters')?></legend>

		<?php echo $okt->Users->filters->getFiltersFields('<div class="three-cols">%s</div>'); ?>

		<p><input type="submit" name="<?php echo $okt->Users->filters->getFilterSubmitName() ?>" value="<?php _e('c_c_action_Display') ?>" />
		<a href="<?php echo $view->generateUrl('Users_index') ?>?init_filters=1"><?php _e('c_c_reset_filters')?></a></p>
	</fieldset>
</form>

<?php if ($rsUsers->isEmpty()) : ?>

	<?php if (!empty($sSearch)) : ?>
	<p><?php _e('m_users_no_searched_user') ?></p>

	<?php elseif ($okt->Users->filters->params->show_filters) : ?>
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
				<a href="<?php echo $view->generateUrl('Users_index') ?>?disable=<?php echo $rsUsers->id ?>"
				class="icon tick"><?php _e('c_c_status_Active')?></a>
				<?php else : ?>
				<a href="<?php echo $view->generateUrl('Users_index') ?>?enable=<?php echo $rsUsers->id ?>"
				class="icon cross"><?php _e('c_c_status_Inactive')?></a>
				<?php endif; ?>
				</li>

				<?php if ($okt->checkPerm('users_edit')) : ?>
				<li><a href="module.php?m=users&amp;action=edit&amp;id=<?php echo $rsUsers->id ?>"
				title="<?php _e('m_users_edit_the_user')?> <?php echo html::escapeHTML($rsUsers->username) ?>"
				class="icon pencil"><?php _e('c_c_action_Edit')?></a></li>
				<?php endif; ?>

				<?php if ($okt->checkPerm('users_delete')) : ?>
				<li><a href="<?php echo $view->generateUrl('Users_index') ?>?delete=<?php echo $rsUsers->id ?>"
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

<?php if ($iNumPages > 1) : ?>
<ul class="pagination"><?php echo $pager->getLinks(); ?></ul>
<?php endif; ?>

<?php endif; ?>
