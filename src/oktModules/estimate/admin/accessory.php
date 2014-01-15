<?php
/**
 * @ingroup okt_module_estimate
 * @brief La page d'administration d'un accessoire
 *
 */

use Okatea\Admin\Page;
use Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


# chargement des locales
$okt->l10n->loadFile(__DIR__.'/../locales/'.$okt->user->language.'/admin.accessories');
$okt->l10n->loadFile(__DIR__.'/../locales/'.$okt->user->language.'/admin.products');


/* Initialisations
----------------------------------------------------------*/

$iAccessoryId = null;
$iProductId = !empty($_REQUEST['product_id']) ? intval($_REQUEST['product_id']) : null;

$sBackUrl = 'module.php?m=estimate&amp;action=accessories';
$sBaseUrl = 'module.php?m=estimate&amp;action=accessory';

if (!empty($iProductId))
{
	if (!$okt->estimate->products->productExists($iProductId))
	{
		$okt->error->set(sprintf(__('m_estimate_product_%s_not_exists'), $iProductId));
		$iProductId = null;
	}
	else {
		$sBackUrl = 'module.php?m=estimate&amp;action=product&amp;product_id='.$iProductId;
	}
}


function appendIdsToUrl($sUrl, $iProductId, $iAccessoryId)
{
	return $sUrl.
		(!empty($iProductId) ? '&amp;product_id='.$iProductId : '').
		(!empty($iAccessoryId) ? '&amp;accessory_id='.$iAccessoryId : '');
}


$aAccessoryData = array(
	'active' => 1,
	'title' => '',
	'product_id' => $iProductId
);

# update an accessory ?
if (!empty($_REQUEST['accessory_id']))
{
	$iAccessoryId = intval($_REQUEST['accessory_id']);

	$rsAccessory = $okt->estimate->accessories->getAccessory($iAccessoryId);

	if ($rsAccessory->isEmpty())
	{
		$okt->error->set(sprintf(__('m_estimate_accessory_%s_not_exists'), $iAccessoryId));
		$iAccessoryId = null;
	}
	else
	{
		$aAccessoryData = array(
			'active' => $rsAccessory->active,
			'title' => $rsAccessory->title,
			'product_id' => $rsAccessory->product_id
		);
	}
}


/* Traitements
----------------------------------------------------------*/

#  ajout / modifications d'un accessoire
if (!empty($_POST['form_sent']))
{
	$aAccessoryData = array(
		'active' => !empty($_POST['p_active']) ? 1 : 0,
		'title' => !empty($_POST['p_title']) ? $_POST['p_title'] : '',
		'product_id' => !empty($_POST['p_product_id']) ? $_POST['p_product_id'] : null
	);

	# update accessory
	if (!empty($iAccessoryId))
	{
		$aAccessoryData['id'] = $iAccessoryId;

		if ($okt->estimate->accessories->updAccessory($aAccessoryData) !== false)
		{
			# log admin
			$okt->logAdmin->info(array(
				'code' => 41,
				'component' => 'estimate',
				'message' => 'accessory #'.$iAccessoryId
			));

			if (!empty($aAccessoryData['product_id']) && !empty($iProductId) && $aAccessoryData['product_id'] != $iProductId) {
				$iProductId = $aAccessoryData['product_id'];
			}

			$okt->page->flash->success(__('m_estimate_accessory_modified'));

			http::redirect($sBaseUrl.'&amp;accessory_id='.$iAccessoryId.(!empty($iProductId) ? '&amp;product_id='.$iProductId : ''));
		}
	}

	# add accessory
	else
	{
		if (($iAccessoryId = $okt->estimate->accessories->addAccessory($aAccessoryData)) !== false)
		{
			# log admin
			$okt->logAdmin->info(array(
				'code' => 40,
				'component' => 'estimate',
				'message' => 'accessory #'.$iAccessoryId
			));

			$okt->page->flash->success(__('m_estimate_accessory_added'));

			http::redirect($sBaseUrl.'&amp;accessory_id='.$iAccessoryId.(!empty($iProductId) ? '&amp;product_id='.$iProductId : ''));
		}
	}
}


/* Affichage
----------------------------------------------------------*/

# Liste des produits
$rsProducts = $okt->estimate->products->getProducts(array('active'=>2));
$aProducts = array('&nbsp;'=>null);
while ($rsProducts->fetch()) {
	$aProducts[html::escapeHTML($rsProducts->title)] = $rsProducts->id;
}
unset($rsProducts);

# Titre de la page
$okt->page->addGlobalTitle(__('m_estimate_accessories'), 'module.php?m=estimate&action=accessories');

if (!empty($iAccessoryId)) {
	$okt->page->addGlobalTitle(__('m_estimate_edit_accessory'));
}
else {
	$okt->page->addGlobalTitle(__('m_estimate_add_accessory'));
}


# button set
$okt->page->setButtonset('estimateAccessoryBtSt', array(
	'id' => 'estimate-accessory-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_Go_back'),
			'url' 			=> $sBackUrl,
			'ui-icon' 		=> 'arrowreturnthick-1-w',
		)
	)
));

if ($iAccessoryId)
{
	$okt->page->addButton('estimateAccessoryBtSt', array(
		'permission' => true,
		'title' => __('m_estimate_add_accessory'),
		'url' => $sBaseUrl.(!empty($iProductId) ? '&amp;product_id='.$iProductId : ''),
		'ui-icon' => 'plusthick'
	));
}

# Validation javascript
$okt->page->validate('accessory-form',array(
	array(
		'id' => 'p_title',
		'rules' => array(
			'required: true',
			'minlength: 2'
		)
	),
	array(
		'id' => 'p_product_id',
		'rules' => array(
			'required: true'
		)
	)
));


# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('estimateAccessoryBtSt'); ?>

<form id="accessory-form" action="module.php" method="post">
	<div class="two-cols">
		<p class="field col"><label for="p_title" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_estimate_accessory_title')?></label>
		<?php echo form::text('p_title', 40, 255, html::escapeHTML($aAccessoryData['title'])) ?></p>

		<p class="field col"><label for="p_product_id" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('m_estimate_accessory_product')?></label>
		<?php echo form::select('p_product_id',$aProducts,$aAccessoryData['product_id'])?></p>

		<p class="field col"><label><?php echo form::checkbox('p_active', 1, $aAccessoryData['active']) ?> <?php _e('c_c_action_visible')?></label></p>
	</div>

	<p><?php echo form::hidden('m', 'estimate'); ?>
	<?php echo form::hidden('action', 'accessory'); ?>
	<?php echo !empty($iAccessoryId) ? form::hidden('accessory_id', $iAccessoryId) : ''; ?>
	<?php echo !empty($iProductId) ? form::hidden('product_id', $iProductId) : ''; ?>
	<?php echo form::hidden('form_sent', 1); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php echo (!empty($iAccessoryId) ? __('c_c_action_edit') : __('c_c_action_add')); ?>" /></p>
</form>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
