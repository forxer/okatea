<?php
/**
 * @ingroup okt_module_partners
 * @brief La page d'accueil du module
 *
 */


use Okatea\Admin\Page;
use Okatea\Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/


/* Traitements
----------------------------------------------------------*/

# switch statut
if (!empty($_GET['switch_status']))
{
	if ($okt->partners->setPartnerStatus($_GET['switch_status'])) {
		http::redirect('module.php?m=partners&action=index&switched=1');
	}
}

# changement de l'ordre des partenaires
$order = array();
if (empty($_POST['partners_order']) && !empty($_POST['order']))
{
	$order = $_POST['order'];
	asort($order);
	$order = array_keys($order);
}
elseif (!empty($_POST['partners_order']))
{
	$order = explode(',',$_POST['partners_order']);
	foreach ($order as $k=>$v) {
		$order[$k] = str_replace('ord_','',$v);
	}
}


if (!empty($_POST['ordered']) && !empty($order))
{
	foreach ($order as $ord=>$partner_id)
	{
		$ord = ((integer) $ord)+1;
		$okt->partners->updPartnersOrder($partner_id, $ord);
	}

	http::redirect('module.php?m=partners&action=index&neworder=1');
}


/* Affichage
----------------------------------------------------------*/

# récupération des partenaires
$rsPartners = $okt->partners->getPartners(array(
	'active' => 2,
	'language' => $okt->user->language
));


# Bouton vers le module côté public
$okt->page->addButton('partnersBtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_action_show'),
	'url' 			=> html::escapeHTML(PartnersHelpers::getPartnersUrl()),
	'ui-icon' 		=> 'extlink'
));

# Sortable
$okt->page->js->addReady("
	$('.sortable').sortable({
		placeholder: 'ui-state-highlight',
		axis: 'y',
		revert: true,
		cursor: 'move'
	});

	$('.sortable').find('input').hide();

	$('#save_order').click(function(){
		var result = '';
		$('.sortable').each(function(){
			if (result.length > 0) {
				result += ',';
			}
			result += $(this).sortable('toArray');
		});

		$('#partners_order').val(result);
	});
");


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('partnersBtSt'); ?>

<?php if ($rsPartners->isEmpty()) : ?>
<p><?php _e('m_partners_no_partner') ?></p>

<?php else : ?>

<form action="module.php" method="post" id="partners-list">

	<?php

	$i = 1;
	$iCurrentCategoryId = 0;
	$iCategoriesCount = 0;

	# si les catégories ne sont pas activées on as quand même besoin d'un <ul>
	if (!$okt->partners->config->enable_categories) {
		echo '<ul class="sortable">';
	}

	while ($rsPartners->fetch()) : ?>

		<?php # si les catégories sont activées on affichent le titre de ces dernières
		if ($okt->partners->config->enable_categories && $iCurrentCategoryId !== $rsPartners->category_id)
		{
			if ($iCategoriesCount > 0) {
				echo '</ul>';
			}

			echo '<h3>'.html::escapeHTML($rsPartners->category_name).'</h3>';
			echo '<ul class="sortable">';

			$iCurrentCategoryId = $rsPartners->category_id;
			$iCategoriesCount++;
		}
		?>

	<li id="ord_<?php echo $rsPartners->id ?>" class="ui-state-default"><label
		for="order_<?php echo $rsPartners->id ?>"> <span
			class="ui-icon ui-icon-arrowthick-2-n-s"></span>
		<?php echo html::escapeHTML($rsPartners->name) ?></label>

		<?php echo form::text(array('order['.$rsPartners->id.']','order_'.$rsPartners->id),5,10,$i++) ?>

		<?php if ($rsPartners->active) : ?>
		- <a
		href="module.php?m=partners&amp;action=index&amp;switch_status=<?php echo $rsPartners->id ?>"
		title="<?php printf(__('c_c_action_Disable_%s'),html::escapeHTML($rsPartners->name)) ?>"
		class="icon tick"><?php _e('c_c_action_Disable') ?></a>
		<?php else : ?>
		- <a
		href="module.php?m=partners&amp;action=index&amp;switch_status=<?php echo $rsPartners->id ?>"
		title="<?php printf(__('c_c_action_Enable_%s'),html::escapeHTML($rsPartners->name)) ?>"
		class="icon cross"><?php _e('c_c_action_Enable') ?></a>
		<?php endif; ?>

		- <a
		href="module.php?m=partners&amp;action=edit&amp;partner_id=<?php echo $rsPartners->id ?>"
		title="<?php printf(__('c_c_action_Edit_%s'),html::escapeHTML($rsPartners->name)) ?>"
		class="icon pencil"><?php _e('c_c_action_Edit') ?></a> - <a
		href="module.php?m=partners&amp;action=delete&amp;partner_id=<?php echo $rsPartners->id ?>"
		onclick="return window.confirm('<?php echo html::escapeJS(__('m_partners_confirm_delete')) ?>')"
		title="<?php printf(__('c_c_action_Delete_%s'),html::escapeHTML($rsPartners->name)) ?>"
		class="icon delete"><?php _e('c_c_action_Delete') ?></a></li>

	<?php endwhile; ?>

	</ul>

	<p><?php echo form::hidden('m','partners'); ?>
	<?php echo form::hidden('action', 'index'); ?>
	<?php echo form::hidden('ordered',1); ?>
	<?php echo form::hidden('partners_order',''); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" id="save_order"
			value="<?php _e('c_c_action_save_order') ?>" />
	</p>
</form>

<?php endif; ?>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>