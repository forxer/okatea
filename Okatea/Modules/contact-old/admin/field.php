<?php
/**
 * @ingroup okt_module_contact
 * @brief Page de gestion d'un champ
 *
 */

use Okatea\Admin\Page;
use Okatea\Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

$field_id = !empty($_REQUEST['field_id']) ? intval($_REQUEST['field_id']) : null;

$do = (!empty($_REQUEST['do']) && $_REQUEST['do'] == 'value') ? 'value' : 'desc';


$field_data = array(
	'id' => $field_id,
	'active' => 0,
	'type' => 1,
	'html_id' => ''
);
$value = array();

foreach ($okt->languages->list as $aLanguage)
{
	$field_data['title'][$aLanguage['code']] = '';
	$field_data['description'][$aLanguage['code']] = '';
	$value[$aLanguage['code']] = '';
}



if (!is_null($field_id))
{
	$rsField = $okt->contact->getField($field_id);
	$rsField_i18n = $okt->contact->getFieldL10n($field_id);

	$field_data = array(
		'id' => $rsField->id,
		'active' => $rsField->active,
		'type' => $rsField->type,
		'html_id' => $rsField->html_id
	);

	foreach ($okt->languages->list as $aLanguage)
	{
		$field_data['title'][$aLanguage['code']] = '';
		$field_data['description'][$aLanguage['code']] = ''	;
		$value[$aLanguage['code']] = '';

		while ($rsField_i18n->fetch())
		{
			if ($rsField_i18n->language == $aLanguage['code'])
			{
				$field_data['title'][$aLanguage['code']] = $rsField_i18n->title;
				$field_data['description'][$aLanguage['code']] = $rsField_i18n->description;

				if (module_contact::getFormType($field_data['type']) == 'simple') {
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
		if (module_contact::getFormType($field_data['type']) == 'simple') {
			$value = (!empty($_POST['p_value']) ? $_POST['p_value'] : array());
		}
		else
		{
			foreach ($okt->languages->list as $aLanguage)
			{
				$value[$aLanguage['code']] = !empty($_POST['p_value'][$aLanguage['code']]) && is_array($_POST['p_value'][$aLanguage['code']]) ? array_filter(array_map('trim',$_POST['p_value'][$aLanguage['code']])) : '';
			}
		}

		if ($okt->contact->setFieldValue($field_id,$value) !== false)
		{
			http::redirect('module.php?m=contact&action=field&do=value&field_id='.$field_id);
		}
	}
	# description champ
	else
	{
		$field_data = array(
			'id' => $field_id,
			'active' => (!empty($_POST['p_active']) ? intval($_POST['p_active']) : ''),
			'type' => (!empty($_POST['p_type']) ? intval($_POST['p_type']) : ''),
			'title' => (!empty($_POST['p_title']) ? $_POST['p_title'] : array()),
			'html_id' => (!empty($_POST['p_html_id']) ? $_POST['p_html_id'] : ''),
			'description' => (!empty($_POST['p_description']) ? $_POST['p_description'] : array()),
		);

		if (empty($field_data['title']['fr'])) {
			$okt->error->set('Vous devez saisir un titre.');
		}

		if (empty($field_data['type'])) {
			$okt->error->set('Vous devez choisir un type.');
		}

		if ($okt->error->isEmpty())
		{
			# modification
			if (!is_null($field_id))
			{
				if ($okt->contact->updField($field_id, $field_data) !== false)
				{
					http::redirect('module.php?m=contact&action=field&do=value&field_id='.$field_id.'&edited=1');
				}
			}
			# ajout
			else
			{
				if (($field_id = $okt->contact->addField($field_data)) !== false)
				{
					http::redirect('module.php?m=contact&action=field&do=value&field_id='.$field_id.'&added=1');
				}
			}


		}
	}
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('m_contact_fields'));

# button set
$okt->page->setButtonset('fieldBtSt',array(
	'id' => 'contact-field-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_Go_back'),
			'url' 			=> 'module.php?m=contact&amp;action=fields',
			'ui-icon' 		=> 'arrowreturnthick-1-w',
		)
	)
));


# liste des types de champs
$aTypes = module_contact::getFieldsTypes();

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#form','.lang-switcher-buttons');
}


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php # buttons set
echo $okt->page->getButtonSet('fieldBtSt'); ?>

<?php # valeur(s) champ
if ($do == 'value') : ?>

<form action="module.php" method="post" id="form">

	<?php if (module_contact::getFormType($field_data['type']) == 'simple') : ?>

		<p>Valeur par défaut du champ intitulé <strong><?php echo html::escapeHTML($field_data['title'][$okt->user->language]) ?></strong>
		de type <em><?php echo $aTypes[$field_data['type']] ?></em></p>

		<?php foreach ($okt->languages->list as $aLanguage) : ?>
		<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_value_<?php echo $aLanguage['code'] ?>">Valeur<span class="lang-switcher-buttons"></span></label>
		<?php echo form::textarea(array('p_value['.$aLanguage['code'].']','p_value_'.$aLanguage['code']),58,5,html::escapeHTML($value[$aLanguage['code']])) ?></p>
		<?php endforeach; ?>

	<?php else : ?>

		<p><?php printf(__('m_contact_value_of_field_named_%s_of_type_%s'), '<strong>'.html::escapeHTML($field_data['title'][$okt->user->language]).'</strong>', '<em>'.$aTypes[$field_data['type']].'</em>')?></p>

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
		<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_value_<?php echo $line_count ?>_<?php echo $aLanguage['code'] ?>"><?php _e('m_contact_Value')?> <?php echo $line_count ?><span class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_value['.$aLanguage['code'].'][]','p_value_'.$aLanguage['code'].'_'.$line_count), 60, 255, html::escapeHTML($sVal)) ?></p>
		<?php endforeach; ?>

		<?php endforeach; ?>

		<?php foreach ($okt->languages->list as $aLanguage) : ?>
		<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_value_<?php echo ($line_count+1) ?>"><?php _e('m_contact_Add_a_value')?><span class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_value['.$aLanguage['code'].'][]','p_value_'.$aLanguage['code'].'_'.($line_count+1)), 60, 255) ?></p>
		<?php endforeach; ?>

	<?php endif; ?>

	<p><?php echo form::hidden(array('form_sent'),1); ?>
	<?php echo form::hidden(array('m'),'contact'); ?>
	<?php echo form::hidden(array('action'), 'field'); ?>
	<?php echo form::hidden(array('do'), 'value'); ?>
	<?php echo form::hidden(array('field_id'), $field_id); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_Save')?>" /></p>
</form>

<?php # description champ
else : ?>

<form action="module.php" method="post" id="form">

	<div class="two-cols">
		<?php foreach ($okt->languages->list as $aLanguage): ?>
		<p class="field col" lang="<?php echo $aLanguage['code'] ?>"><label for="p_title_<?php echo $aLanguage['code'] ?>" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_c_Title')?><span class="lang-switcher-buttons"></span></label>
		<?php echo form::text(array('p_title['.$aLanguage['code'].']','p_title_'.$aLanguage['code']),60,255,html::escapeHTML($field_data['title'][$aLanguage['code']]))?></p>
		<?php endforeach; ?>

		<p class="field col"><label for="p_html_id"><?php _e('m_contact_html_id')?></label>
		<?php echo form::text('p_html_id',60,255,html::escapeHTML($field_data['html_id']))?></p>
	</div>
	<div class="two-cols">
		<p class="field col"><label for="p_type" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_contact_Type')?></label>
		<?php echo form::select('p_type',module_contact::getFieldsTypes(true),$field_data['type'])?></p>

		<p class="field col"><label for="p_active" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('Status')?></label>
		<?php echo form::select('p_active',module_contact::getFieldsStatus(true,in_array($field_id,module_contact::getUnDisablableFields())),$field_data['active'])?></p>
	</div>

	<?php foreach ($okt->languages->list as $aLanguage) : ?>
	<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_description_<?php echo $aLanguage['code'] ?>"><?php _e('c_c_Description')?><span class="lang-switcher-buttons"></span></label>
	<?php echo form::textarea(array('p_description['.$aLanguage['code'].']','p_description_'.$aLanguage['code']),58,5,html::escapeHTML($field_data['description'][$aLanguage['code']])) ?></p>
	<?php endforeach; ?>

	<p><?php echo form::hidden(array('form_sent'),1); ?>
	<?php echo form::hidden(array('m'),'contact'); ?>
	<?php echo form::hidden(array('action'), 'field'); ?>
	<?php echo form::hidden(array('do'), 'desc'); ?>
	<?php echo form::hidden(array('field_id'), $field_id); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="suivant" /></p>
</form>

<?php endif; ?>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>

