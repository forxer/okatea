<?php
/**
 * @ingroup okt_module_contact
 * @brief Page de gestion des champs
 *
 */


use Tao\Admin\Page;
use Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_CONTACT_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/


/* Traitements
----------------------------------------------------------*/

# suppression d'un champ
if (!empty($_GET['delete']))
{
	if ($okt->contact->delField($_GET['delete']))
	{
		$okt->page->flashMessages->addSuccess(__('m_contact_fields_deleted'));

		http::redirect('module.php?m=contact&action=fields');
	}
}

# enregistrement de l'ordre des champs
$order = array();
if (empty($_POST['fields_order']) && !empty($_POST['order']))
{
	$order = $_POST['order'];
	asort($order);
	$order = array_keys($order);
}
elseif (!empty($_POST['fields_order']))
{
	$order = explode(',',$_POST['fields_order']);
	foreach ($order as $k=>$v) {
		$order[$k] = str_replace('ord_','',$v);
	}
}

if (!empty($_POST['ordered']) && !empty($order))
{
	foreach ($order as $ord=>$id)
	{
		$ord = ((integer) $ord)+1;
		$okt->contact->updFieldOrder($id,$ord);
	}

	$okt->page->flashMessages->addSuccess(__('m_contact_neworder'));

	http::redirect('module.php?m=contact&action=fields');
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('m_contact_fields'));

# button set
$okt->page->setButtonset('fieldsBtSt',array(
	'id' => 'contact-fields-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' => true,
			'title' => __('m_contact_fields_add'),
			'url' => 'module.php?m=contact&amp;action=field',
			'ui-icon' => 'plusthick',
			'active' => false
		)
	)
));

# liste des champs
$rsFields = $okt->contact->getFields(array("language" => $okt->user->language));

# liste des types de champs
$aTypes = module_contact::getFieldsTypes();

# liste des statut de champs
$aStatus = module_contact::getFieldsStatus();


# Sortable
$okt->page->js->addReady("
	$('#sortable').sortable({
		placeholder: 'ui-state-highlight',
		axis: 'y',
		revert: true,
		cursor: 'move'
	});

	$('#sortable').find('input').hide();

	$('#save_order').click(function(){
		var result = $('#sortable').sortable('toArray');
		$('#fields_order').val(result);
	});
");


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php # buttons set
echo $okt->page->getButtonSet('fieldsBtSt'); ?>


<?php if ($rsFields->isEmpty()) : ?>
<p><?php _e('m_contact_fields_no_field') ?></p>

<?php else : ?>

<form action="module.php" method="post" id="ordering">
	<ul id="sortable" class="ui-sortable">
	<?php $i = 1;
	while ($rsFields->fetch()) : ?>
	<li id="ord_<?php echo $rsFields->id; ?>" class="ui-state-default two-cols">

		<div class="col">
			<label for="order_<?php echo $rsFields->id ?>">

			<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>

			<?php echo html::escapeHTML($rsFields->title) ?></label>

			<?php echo form::text(array('order['.$rsFields->id.']','order_'.$rsFields->id),5,10,$i++) ?>

			(<?php echo $aTypes[$rsFields->type] ?> - <?php echo $aStatus[$rsFields->active] ?>)

		</div>
		<div class="col right">
<!-- trois valeurs !
			<?php if ($rsFields->active) : ?>
			- <a href="module.php?m=contact&action=fields&amp;switch_status=<?php echo $rsFields->id ?>"
			title="<?php printf(__('c_c_action_Disable_%s'),html::escapeHTML($rsFields->title)) ?>"
			class="icon tick"><?php _e('c_c_action_Disable') ?></a>
			<?php else : ?>
			- <a href="module.php?m=contact&action=fields&amp;switch_status=<?php echo $rsFields->id ?>"
			title="<?php printf(__('c_c_action_Enable_%s'),html::escapeHTML($rsFields->title)) ?>"
			class="icon cross"><?php _e('c_c_action_Enable') ?></a>
			<?php endif; ?>
-->
			<a href="module.php?m=contact&amp;action=field&amp;field_id=<?php echo $rsFields->id ?>"
			title="<?php _e('m_contact_modify_field_destination')?> <?php echo html::escapeHTML($rsFields->title) ?>"
			class="icon pencil"><?php _e('m_contact_modify_definition')?></a>

			- <a href="module.php?m=contact&amp;action=field&amp;do=value&amp;field_id=<?php echo $rsFields->id ?>"
			title="<?php _e('m_contact_modify_field_value')?> <?php echo html::escapeHTML($rsFields->title) ?>"
			class="icon paintbrush"><?php _e('m_contact_modify_value')?></a>

			<?php if (in_array($rsFields->id,module_contact::getUnDeletableFields())) : ?>
			- <a class="icon delete disabled"><?php _e('c_c_action_Delete') ?></a>
			<?php else : ?>
			- <a href="module.php?m=contact&amp;action=fields&amp;delete=<?php echo $rsFields->id ?>"
			onclick="return window.confirm('<?php echo html::escapeJS(__('m_contact_confirm_field_deletion')) ?>')"
			title="Supprimer le champ <?php echo html::escapeHTML($rsFields->title) ?>"
			class="icon delete"><?php _e('c_c_action_Delete') ?></a>
			<?php endif; ?>
		</div>

	</li>
	<?php endwhile; ?>
	</ul>

	<p><?php echo form::hidden(array('m'),'contact'); ?>
	<?php echo form::hidden(array('action'), 'fields'); ?>
	<?php echo form::hidden('ordered',1); ?>
	<?php echo form::hidden('fields_order',''); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save_order') ?>" id="save_order" /></p>
</form>
<?php endif; ?>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>

