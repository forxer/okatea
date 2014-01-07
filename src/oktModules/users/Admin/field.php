<?php
/**
 * @ingroup okt_module_users
 * @brief Page de gestion d'un champ personnalisé
 *
 */

use Tao\Admin\Page;
use Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

$iFieldId = !empty($_REQUEST['field_id']) ? intval($_REQUEST['field_id']) : null;

$do = (!empty($_REQUEST['do']) && $_REQUEST['do'] == 'value') ? 'value' : 'desc';


$aFieldData = array(
	'id' => $iFieldId,
	'status' => 0,
	'register_status' => 0,
	'user_editable' => 0,
	'type' => 1,
	'html_id' => ''
);
$value = array();

foreach ($okt->languages->list as $aLanguage)
{
	$aFieldData['title'][$aLanguage['code']] = '';
	$aFieldData['description'][$aLanguage['code']] = '';
	$value[$aLanguage['code']] = '';
}


if (!is_null($iFieldId))
{
	$rsField = $okt->users->fields->getField($iFieldId);
	$rsField_i18n = $okt->users->fields->getFieldI18n($iFieldId);

	$aFieldData = array(
		'id' => $rsField->id,
		'status' => $rsField->status,
		'register_status' => $rsField->register_status,
		'user_editable' => $rsField->user_editable,
		'type' => $rsField->type,
		'html_id' => $rsField->html_id
	);

	foreach ($okt->languages->list as $aLanguage)
	{
		$aFieldData['title'][$aLanguage['code']] = '';
		$aFieldData['description'][$aLanguage['code']] = '';
		$value[$aLanguage['code']] = '';

		while ($rsField_i18n->fetch())
		{
			if ($rsField_i18n->language == $aLanguage['code'])
			{
				$aFieldData['title'][$aLanguage['code']] = $rsField_i18n->title;
				$aFieldData['description'][$aLanguage['code']] = $rsField_i18n->description;

				if (UsersCustomFields::getFormType($aFieldData['type']) == 'simple') {
					$value[$aLanguage['code']] = $rsField_i18n->value;
				}
				else {
					$value[$aLanguage['code']] = array_filter((array)unserialize($rsField_i18n->value));
				}
			}
		}
	}

	unset($rsField);
}


/* Traitements
----------------------------------------------------------*/

if (!empty($_POST['form_sent']))
{
	# valeur(s) champ
	if ($do == 'value')
	{
		if (UsersCustomFields::getFormType($aFieldData['type']) == 'simple') {
			$value = (!empty($_POST['p_value']) ? $_POST['p_value'] : array());
		}
		else
		{
			foreach ($okt->languages->list as $aLanguage) {
				$value[$aLanguage['code']] = !empty($_POST['p_value'][$aLanguage['code']]) && is_array($_POST['p_value'][$aLanguage['code']]) ? array_filter(array_map('trim',$_POST['p_value'][$aLanguage['code']])) : '';
			}
		}

		if ($okt->users->fields->setFieldValue($iFieldId,$value) !== false) {
			http::redirect('module.php?m=users&action=field&do=value&field_id='.$iFieldId.'&edited=1');
		}
	}
	# description champ
	else
	{
		$aFieldData = array(
			'id' => $iFieldId,
			'status' => (!empty($_POST['p_status']) ? intval($_POST['p_status']) : ''),
			'register_status' => (!empty($_POST['p_register_status']) ? true : false),
			'user_editable' => (!empty($_POST['p_user_editable']) ? true : false),
			'type' => (!empty($_POST['p_type']) ? intval($_POST['p_type']) : ''),
			'title' => (!empty($_POST['p_title']) ? $_POST['p_title'] : array()),
			'html_id' => (!empty($_POST['p_html_id']) ? $_POST['p_html_id'] : ''),
			'description' => (!empty($_POST['p_description']) ? $_POST['p_description'] : array())
		);

		foreach ($okt->languages->list as $aLanguage)
		{
			if (empty($aFieldData['title'][$aLanguage['code']])) {
				$okt->error->set('Vous devez saisir un titre en '.$aLanguage['title'].'.');
			}
		}

		if (empty($aFieldData['type'])) {
			$okt->error->set('Vous devez choisir un type.');
		}

		if ($okt->error->isEmpty())
		{
			# modification
			if (!is_null($iFieldId))
			{
				if ($okt->users->fields->updField($iFieldId, $aFieldData) !== false) {
					http::redirect('module.php?m=users&action=field&do=value&field_id='.$iFieldId.'&edited=1');
				}
			}
			# ajout
			else
			{
				if (($iFieldId = $okt->users->fields->addField($aFieldData)) !== false) {
					http::redirect('module.php?m=users&action=field&do=value&field_id='.$iFieldId.'&added=1');
				}
			}


		}
	}
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('m_users_Custom_fields'));

# button set
$okt->page->setButtonset('fieldBtSt',array(
	'id' => 'users-field-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_Go_back'),
			'url' 			=> 'module.php?m=users&amp;action=fields',
			'ui-icon' 		=> 'arrowreturnthick-1-w',
		)
	)
));


# liste des types de champs
$aTypes = UsersCustomFields::getFieldsTypes();

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#form','.lang-switcher-buttons');
}

$okt->page->js->addReady('

	function toggleRegisterStatus()
	{
		if ($("#p_user_editable").is(":checked")) {
			$("#p_register_status").removeAttr("disabled").parent().removeClass("disabled");
		}
		else {
			$("#p_register_status").attr("disabled", "disabled").parent().addClass("disabled");
		}
	}

	$("#p_user_editable").change(function(){
		toggleRegisterStatus();
	});

	toggleRegisterStatus();

');



# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php # buttons set
echo $okt->page->getButtonSet('fieldBtSt'); ?>

<?php # valeur(s) champ
if ($do == 'value') : ?>

<form action="module.php" method="post" id="form">

	<?php if (UsersCustomFields::getFormType($aFieldData['type']) == 'simple') : ?>

		<p>Valeur par défaut du champ intitulé <strong><?php echo html::escapeHTML($aFieldData['title'][$okt->user->language]) ?></strong>
		de type <em><?php echo $aTypes[$aFieldData['type']] ?></em></p>

		<?php foreach ($okt->languages->list as $aLanguage) : ?>
		<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_value_<?php echo $aLanguage['code'] ?>">Valeur<span class="lang-switcher-buttons"></span></label>
		<?php echo form::textarea(array('p_value['.$aLanguage['code'].']', 'p_value_'.$aLanguage['code']), 58, 5, html::escapeHTML($value[$aLanguage['code']])) ?></p>
		<?php endforeach; ?>

	<?php else : ?>

		<p><?php printf(__('m_users_value_of_field_named_%s_of_type_%s'), '<strong>'.html::escapeHTML($aFieldData['title'][$okt->user->language]).'</strong>', '<em>'.$aTypes[$aFieldData['type']].'</em>')?></p>

		<?php $line_count = 0;
		foreach ($value[$okt->user->language] as $val) :
			$line_count++; ?>

		<?php foreach ($okt->languages->list as $aLanguage) :
		if($aLanguage['code'] != $okt->user->language){
			$key = $line_count - 1;
			$sVal = array_key_exists($key, $value[$aLanguage['code']]) ? $value[$aLanguage['code']][$key] : '';
		}else{
			$sVal = $val;
		}
		?>
		<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_value_<?php echo $line_count ?>_<?php echo $aLanguage['code'] ?>"><?php _e('m_users_Value')?> <?php echo $line_count ?><span class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_value['.$aLanguage['code'].'][]', 'p_value_'.$aLanguage['code'].'_'.$line_count), 60, 255, html::escapeHTML($sVal)) ?></p>
		<?php endforeach; ?>

		<?php endforeach; ?>

		<?php foreach ($okt->languages->list as $aLanguage) : ?>
		<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_value_<?php echo ($line_count+1) ?>"><?php _e('m_users_Add_a_value')?><span class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_value['.$aLanguage['code'].'][]', 'p_value_'.$aLanguage['code'].'_'.($line_count+1)), 60, 255) ?></p>
		<?php endforeach; ?>

	<?php endif; ?>

	<p><?php echo form::hidden(array('form_sent'),1); ?>
	<?php echo form::hidden(array('m'),'users'); ?>
	<?php echo form::hidden(array('action'), 'field'); ?>
	<?php echo form::hidden(array('do'), 'value'); ?>
	<?php echo form::hidden(array('field_id'), $iFieldId); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_Save')?>" /></p>
</form>

<?php # description champ
else : ?>

<form action="module.php" method="post" id="form">

	<div class="two-cols">
		<?php foreach ($okt->languages->list as $aLanguage): ?>
		<p class="field col" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_Title')?><span class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']), 60, 255, html::escapeHTML($aFieldData['title'][$aLanguage['code']]))?></p>
		<?php endforeach; ?>

		<p class="field col"><label for="p_html_id"><?php _e('m_users_html_id')?></label>
		<?php echo form::text('p_html_id', 60, 255, html::escapeHTML($aFieldData['html_id']))?></p>
	</div>
	<div class="two-cols">
		<p class="field col"><label for="p_type" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_users_Type')?></label>
		<?php echo form::select('p_type', UsersCustomFields::getFieldsTypes(true), $aFieldData['type'])?></p>

		<p class="field col"><label for="p_status" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('Status') ?></label>
		<?php echo form::select('p_status', UsersCustomFields::getFieldsStatus(true), $aFieldData['status'])?></p>
	</div>
	<div class="two-cols">
		<div class="col">
			<?php foreach ($okt->languages->list as $aLanguage) : ?>
			<p class="field col" lang="<?php echo $aLanguage['code'] ?>"><label for="p_description_<?php echo $aLanguage['code'] ?>"><?php _e('c_c_Description')?><span class="lang-switcher-buttons"></span></label>
			<?php echo form::textarea(array('p_description['.$aLanguage['code'].']','p_description_'.$aLanguage['code']), 58, 5, html::escapeHTML($aFieldData['description'][$aLanguage['code']])) ?></p>
			<?php endforeach; ?>
		</div>
		<div class="col">
			<p class="field"><label for="p_user_editable"><?php echo form::checkbox('p_user_editable', 1, $aFieldData['user_editable']) ?>
			Modifiable par les utilisateurs</label></p>

			<p class="field"><label for="p_register_status"><?php echo form::checkbox('p_register_status', 1, $aFieldData['register_status']) ?>
			Afficher sur la page d'inscription</label></p>
		</div>
	</div><!-- .two-cols -->

	<p><?php echo form::hidden(array('form_sent'),1); ?>
	<?php echo form::hidden(array('m'),'users'); ?>
	<?php echo form::hidden(array('action'), 'field'); ?>
	<?php echo form::hidden(array('do'), 'desc'); ?>
	<?php echo form::hidden(array('field_id'), $iFieldId); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="suivant" /></p>
</form>

<?php endif; ?>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>

