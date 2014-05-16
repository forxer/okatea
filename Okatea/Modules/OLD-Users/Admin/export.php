<?php
/**
 * @ingroup okt_module_users
 * @brief La page d'export des utilisateurs
 *
 */
use Okatea\Admin\Page;
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Users\Authentification;

# Accès direct interdit
if (! defined('ON_MODULE'))
	die();
	
	/* Initialisations
----------------------------------------------------------*/
	
# Format d'export autorisés
$aAllowedFormats = module_users::getAllowedFormats();

# Champs exportables autorisés
$aAllowedFields = module_users::getAllowedFields();

# Liste des groupes exportables
$params = array(
	'group_id_not' => array(
		Authentification::guest_group_id,
		Authentification::superadmin_group_id
	)
);

$rsGroups = $okt->users->getGroups($params);

$aGroups = array();
while ($rsGroups->fetch())
{
	$aGroups[$rsGroups->group_id] = $rsGroups->title;
}

unset($rsGroups);

$p_group = array();
$p_field = array();
$p_format = null;

/* Traitements
----------------------------------------------------------*/

if (! empty($_POST['form_sent']))
{
	$p_group = ! empty($_POST['p_group']) && is_array($_POST['p_group']) ? array_map('intval', $_POST['p_group']) : array();
	$p_field = ! empty($_POST['p_field']) && is_array($_POST['p_field']) ? $_POST['p_field'] : array();
	$p_format = ! empty($_POST['p_format']) && array_key_exists($_POST['p_format'], $aAllowedFormats) ? $_POST['p_format'] : null;
	
	# we need at least one group
	if (empty($p_group))
	{
		$okt->error->set(__('m_users_must_select_at_least_one_group'));
	}
	else
	{
		foreach ($p_group as $gid)
		{
			if (! array_key_exists($gid, $aGroups))
			{
				$okt->error->set(sprintf(__('m_users_group_%s_not_exportable'), $gid));
			}
		}
	}
	
	# we need at least one field
	if (empty($p_field))
	{
		$okt->error->set(__('m_users_must_select_at_least_one_data_type'));
	}
	else
	{
		foreach ($p_field as $field)
		{
			if (! array_key_exists($field, $aAllowedFields))
			{
				$okt->error->set(sprinf(__('m_users_data_type_%s_not_exportable'), html::escapeHTML($field)));
			}
		}
	}
	
	# we need a format
	if (empty($p_format))
	{
		$okt->error->set(__('m_users_must_select_valid_format'));
	}
	
	# do export
	if ($okt->error->isEmpty())
	{
		$okt->users->export($p_format, $p_field, $p_group);
		http::redirect('module.php?m=users&action=export&done=1');
	}
}

/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('m_users_Export'));

# En-tête
require OKT_ADMIN_HEADER_FILE;
?>

<form action="module.php" method="post">

	<fieldset>
		<legend><?php _e('m_users_select_group_to_export')?></legend>

		<ul class="field">
			<?php foreach ($aGroups as $gid=>$gtitle) : ?>
			<li><label><?php echo form::checkbox(array('p_group[]','p_group_'.$gid),$gid,in_array($gid,$p_group))?>
			<?php echo html::escapeHTML($gtitle) ?></label></li>
			<?php endforeach; ?>
		</ul>

	</fieldset>

	<fieldset>
		<legend><?php _e('m_users_select_data_to_export')?></legend>

		<ul class="field">
			<?php foreach ($aAllowedFields as $fid=>$ftitle) : ?>
			<li><label><?php echo form::checkbox(array('p_field[]','p_field_'.$fid),$fid,in_array($fid,$p_field))?>
			<?php echo html::escapeHTML($ftitle) ?></label></li>
			<?php endforeach; ?>
		</ul>

	</fieldset>

	<p class="field">
		<label for="p_format"><?php _e('m_users_Format')?></label>
	<?php echo form::select('p_format',array_merge(array('&nbsp;'=>null),array_flip($aAllowedFormats)),$p_format) ?></p>

	<p><?php echo form::hidden('action','export')?>
	<?php echo form::hidden('m','users')?>
	<?php echo form::hidden('form_sent',1)?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('m_users_action_Export') ?>" />
	</p>
</form>

<?php 
# Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
