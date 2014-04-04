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
			'title' 	=> __('m_contact_fields_add_field'),
			'url' 		=> $view->generateUrl('Contact_field_add'),
			'ui-icon' 	=> 'plusthick',
			'status' 	=> false
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
$okt->page->js->addReady('
	$("#sortable").sortable({
		placeholder: "ui-state-highlight",
		axis: "y",
		revert: true,
		cursor: "move",
		change: function(event, ui) {
			$("#page,#sortable").css("cursor", "progress");
		},
		update: function(event, ui) {
			var result = $("#sortable").sortable("serialize");

			$.ajax({
				data: result,
				url: "'.$view->generateUrl('Contact_fields').'?ajax_update_order=1",
				success: function(data) {
					$("#page").css("cursor", "default");
					$("#sortable").css("cursor", "move");
				},
				error: function(data) {
					$("#page").css("cursor", "default");
					$("#sortable").css("cursor", "move");
				}
			});
		}
	});

	$("#sortable").find("input").hide();
	$("#save_order").hide();
	$("#sortable").css("cursor", "move");
');

?>

<?php # buttons set
echo $okt->page->getButtonSet('fieldsBtSt'); ?>


<?php if ($rsFields->isEmpty()) : ?>
<p><?php _e('m_contact_fields_no_field') ?></p>

<?php else : ?>

<form action="<?php echo $view->generateUrl('Contact_fields') ?>" method="post" id="ordering">
	<ul id="sortable" class="ui-sortable">
	<?php $i = 1;
	while ($rsFields->fetch()) : ?>
	<li id="ord_<?php echo $rsFields->id; ?>" class="ui-state-default two-cols">

		<div class="col">
			<label for="p_order_<?php echo $rsFields->id ?>">

			<span class="ui-icon ui-icon-arrowthick-2-n-s"></span>

			<?php echo $view->escape($rsFields->title) ?></label>

			<?php echo form::text(array('p_order['.$rsFields->id.']', 'p_order_'.$rsFields->id), 5, 10, $i++) ?>

			(<?php echo $aTypes[$rsFields->type] ?> - <?php echo $aStatus[$rsFields->status] ?>)

		</div>
		<div class="col right">

			<a href="<?php echo $view->generateUrl('Contact_field', array('field_id' => $rsFields->id)) ?>"
			title="<?php echo $view->escapeHtmlAttr(sprintf(__('m_contact_fields_edit_field_definition'), $rsFields->title)) ?>"
			class="icon pencil"><?php _e('m_contact_fields_edit_definition') ?></a>

			- <a href="<?php echo $view->generateUrl('Contact_field_values', array('field_id' => $rsFields->id)) ?>"
			title="<?php echo $view->escapeHtmlAttr(sprintf(__('m_contact_fields_edit_field_values'), $rsFields->title)) ?>"
			class="icon paintbrush"><?php _e('m_contact_fields_edit_values') ?></a>

			<?php if (in_array($rsFields->id, Fields::getUnDeletableFields())) : ?>
			- <a class="icon delete disabled"><?php _e('c_c_action_Delete') ?></a>
			<?php else : ?>
			- <a href="<?php echo $view->generateUrl('Contact_fields') ?>?delete=<?php echo $rsFields->id ?>"
			onclick="return window.confirm('<?php echo $view->escapeJs(__('m_contact_fields_confirm_field_deletion')) ?>')"
			title="<?php echo $view->escapeHtmlAttr(sprintf(__('m_contact_fields_delete_field'), $rsFields->title)) ?>"
			class="icon delete"><?php _e('c_c_action_Delete') ?></a>
			<?php endif; ?>
		</div>

	</li>
	<?php endwhile; ?>
	</ul>

	<p><?php echo form::hidden('ordered', 1); ?>
	<?php echo form::hidden('order_fields', 1); ?>
	<?php echo $okt->page->formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save_order') ?>" id="save_order" /></p>
</form>
<?php endif; ?>
