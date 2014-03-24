<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Modules\Contact\Fields;
use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');


# Module title tag
$okt->page->addTitleTag($okt->module('Contact')->getTitle());

# Module start breadcrumb
$okt->page->addAriane($okt->module('Contact')->getName(), $view->generateUrl('Contact_index'));

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
$rsFields = $okt->module('Contact')->fields->getFields(array("language" => $okt->user->language));

# liste des types de champs
$aTypes = Fields::getFieldsTypes();

# liste des statut de champs
$aStatus = Fields::getFieldsStatus();


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
?>

<?php # buttons set
echo $okt->page->getButtonSet('fieldsBtSt'); ?>


<?php if ($rsFields->isEmpty()) : ?>
<p><?php _e('m_contact_fields_no_field') ?></p>

<?php else : ?>

<form action="<?php $view->generateUrl('Contact_fields') ?>" method="post" id="ordering">
	<ul id="sortable" class="ui-sortable">
	<?php $i = 1;
	while ($rsFields->fetch()) : ?>
	<li id="ord_<?php echo $rsFields->id; ?>" class="ui-state-default two-cols">

		<div class="col">
			<label for="order_<?php echo $rsFields->id ?>">

			<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>

			<?php echo $view->escape($rsFields->title) ?></label>

			<?php echo form::text(array('order['.$rsFields->id.']','order_'.$rsFields->id),5,10,$i++) ?>

			(<?php echo $aTypes[$rsFields->type] ?> - <?php echo $aStatus[$rsFields->active] ?>)

		</div>
		<div class="col right">
<!-- trois valeurs !
			<?php if ($rsFields->active) : ?>
			- <a href="module.php?m=contact&action=fields&amp;switch_status=<?php echo $rsFields->id ?>"
			title="<?php printf(__('c_c_action_Disable_%s'), $view->escape($rsFields->title)) ?>"
			class="icon tick"><?php _e('c_c_action_Disable') ?></a>
			<?php else : ?>
			- <a href="module.php?m=contact&action=fields&amp;switch_status=<?php echo $rsFields->id ?>"
			title="<?php printf(__('c_c_action_Enable_%s'), $view->escape($rsFields->title)) ?>"
			class="icon cross"><?php _e('c_c_action_Enable') ?></a>
			<?php endif; ?>
-->
			<a href="module.php?m=contact&amp;action=field&amp;field_id=<?php echo $rsFields->id ?>"
			title="<?php _e('m_contact_modify_field_destination') ?> <?php echo $view->escape($rsFields->title) ?>"
			class="icon pencil"><?php _e('m_contact_modify_definition')?></a>

			- <a href="module.php?m=contact&amp;action=field&amp;do=value&amp;field_id=<?php echo $rsFields->id ?>"
			title="<?php _e('m_contact_modify_field_value') ?> <?php echo $view->escape($rsFields->title) ?>"
			class="icon paintbrush"><?php _e('m_contact_modify_value')?></a>

			<?php if (in_array($rsFields->id, Fields::getUnDeletableFields())) : ?>
			- <a class="icon delete disabled"><?php _e('c_c_action_Delete') ?></a>
			<?php else : ?>
			- <a href="module.php?m=contact&amp;action=fields&amp;delete=<?php echo $rsFields->id ?>"
			onclick="return window.confirm('<?php echo $view->escapeJs(__('m_contact_confirm_field_deletion')) ?>')"
			title="Supprimer le champ <?php echo $view->escape($rsFields->title) ?>"
			class="icon delete"><?php _e('c_c_action_Delete') ?></a>
			<?php endif; ?>
		</div>

	</li>
	<?php endwhile; ?>
	</ul>

	<p>
	<?php echo form::hidden('ordered',1); ?>
	<?php echo form::hidden('fields_order',''); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save_order') ?>" id="save_order" /></p>
</form>
<?php endif; ?>