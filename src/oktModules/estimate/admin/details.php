<?php
/**
 * @ingroup okt_module_estimate
 * @brief La page d'administration des devis
 *
 */

use Tao\Misc\Utilities as util;

# Accès direct interdit
if (!defined('ON_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

# Chargement des locales
$okt->l10n->loadFile(__DIR__.'/../locales/'.$okt->user->language.'/admin.details');

$iEstimateId = !empty($_REQUEST['estimate_id']) ? intval($_REQUEST['estimate_id']) : null;

$rsEstimate = $okt->estimate->getEstimate($iEstimateId);

if (is_null($iEstimateId) || $rsEstimate->isEmpty())
{
	$okt->page->flashMessages->addError(sprintf(__('m_estimate_estimate_%s_not_exists'), $iEstimateId));

	http::redirect('module.php?m=estimate&action=index');
}


/* Traitements
----------------------------------------------------------*/


# Marque la demande comme traitée
if (!empty($_GET['treated']))
{
	try
	{
		$okt->estimate->markAsTreated($iEstimateId);

		# log admin
		$okt->logAdmin->info(array(
			'code' => 32,
			'component' => 'estimate',
			'message' => 'estimate #'.$iEstimateId
		));

		$okt->page->flashMessages->addSuccess(__('m_estimate_details_marked_as_treated'));

		http::redirect('module.php?m=estimate&action=details&estimate_id='.$iEstimateId);
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}

# Marque la demande comme non traitée
if (!empty($_GET['untreated']))
{
	try
	{
		$okt->estimate->markAsUntreated($iEstimateId);

		# log admin
		$okt->logAdmin->info(array(
			'code' => 32,
			'component' => 'estimate',
			'message' => 'estimate #'.$iEstimateId
		));

		$okt->page->flashMessages->addSuccess(__('m_estimate_details_marked_as_untreated'));

		http::redirect('module.php?m=estimate&action=details&estimate_id='.$iEstimateId);
	}
	catch (Exception $e) {
		$okt->error->set($e->getMessage());
	}
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(sprintf(__('m_estimate_details_%s'), $iEstimateId));


# button set
$okt->page->setButtonset('estimateBtSt', array(
	'id' => 'estimate-buttonset',
	'type' => '', #  buttonset-single | buttonset-multi | ''
	'buttons' => array(
		array(
			'permission' 	=> true,
			'title' 		=> __('c_c_action_Go_back'),
			'url' 			=> 'module.php?m=estimate&amp;action=index',
			'ui-icon' 		=> 'arrowreturnthick-1-w'
		),
		array(
			'permission' 	=> ($rsEstimate->status == 1),
			'title' 		=> __('m_estimate_details_treated'),
			'url' 			=> 'module.php?m=estimate&amp;action=details&amp;untreated=1&amp;estimate_id='.$iEstimateId,
			'ui-icon' 		=> 'check',
			'onclick' 		=> 'return window.confirm(\''.html::escapeJS(sprintf(__('m_estimate_details_mark_%s_as_untreated'), $iEstimateId)).'\')'
		),
		array(
			'permission' 	=> ($rsEstimate->status == 0),
			'title' 		=> __('m_estimate_details_untreated'),
			'url' 			=> 'module.php?m=estimate&amp;action=details&amp;treated=1&amp;estimate_id='.$iEstimateId,
			'ui-icon' 		=> 'clock',
			'onclick' 		=> 'return window.confirm(\''.html::escapeJS(sprintf(__('m_estimate_details_mark_%s_as_treated'), $iEstimateId)).'\')'
		),
		array(
			'permission' 	=> true,
			'title' 		=> __('m_estimate_details_delete'),
			'url' 			=> 'module.php?m=estimate&amp;action=delete&amp;estimate_id='.$iEstimateId,
			'ui-icon' 		=> 'closethick',
			'onclick' 		=> 'return window.confirm(\''.html::escapeJS(__('m_estimate_details_delete_confirm')).'\')'
		)
	)
));

# CSS
$okt->page->css->addCss('
.product_wrapper {
	margin: 0 0 1em;
	padding: 0.5em 0.8em;
}
	.product_line {
	}
		.product_title {
			font-weight: bold;
			float: left;
			width: 60%;
			border-bottom: 1px dotted #606060;
		}
		.product_quantity {
			font-weight: bold;
			float: left;
			width: 38%;
		}

	.accessories_wrapper {
		margin: 0.8em 0 0;
		padding: 0 0.5em;
	}
		.accessory_line {
			padding: 0.3em 0;
		}
			.accessory_title {
				float: left;
				width: 60%;
			border-bottom: 1px dotted #b0b0b0;
			}
			.accessory_quantity {
				float: left;
				width: 38%;
			}


');

# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('estimateBtSt'); ?>

<div class="two-cols">
	<div class="col">
		<h3><?php _e('m_estimate_details_user_infos') ?></h3>

		<p><?php echo html::escapeHTML($rsEstimate->content['firstname'].' '.$rsEstimate->content['lastname']) ?></p>
		<p><a href="mailto:<?php echo html::escapeHTML($rsEstimate->content['email']) ?>"><?php echo html::escapeHTML($rsEstimate->content['email']) ?></a></p>
		<p><?php echo html::escapeHTML($rsEstimate->content['phone']) ?></p>
	</div>
	<div class="col">
		<h3><?php _e('m_estimate_details_projected_dates') ?></h3>

			<p>
				<?php if ($rsEstimate->start_at == $rsEstimate->end_at) : ?>
				<?php printf(__('On %s'), dt::dt2str(__('%A, %B %d, %Y'), html::escapeHTML($rsEstimate->start_at))) ?>

				<?php else : ?>
				<?php printf(__('From %s to %s'),
					dt::dt2str(__('%A, %B %d, %Y'), html::escapeHTML($rsEstimate->start_at)),
					dt::dt2str(__('%A, %B %d, %Y'), html::escapeHTML($rsEstimate->end_at))
				); ?>
				<?php endif; ?>
			</p>
	</div>
</div>


<?php if ($okt->estimate->config->enable_accessories) : ?>
<h3><?php _e('m_estimate_details_products_accessories') ?></h3>

<?php else : ?>
<h3><?php _e('m_estimate_details_products') ?></h3>

<?php endif; ?>

	<?php foreach ($rsEstimate->content['products'] as $aProduct) : ?>
	<div class="product_wrapper">
		<div class="product_line ui-helper-clearfix">
			<div class="product_title"><?php echo html::escapeHTML($aProduct['title']) ?></div>
			<div class="product_quantity"><?php echo html::escapeHTML($aProduct['quantity']) ?></div>
		</div>

		<?php if ($okt->estimate->config->enable_accessories && !empty($aProduct['accessories'])) : ?>
		<div class="accessories_wrapper">
			<?php foreach ($aProduct['accessories'] as $aAccessory) : ?>
			<div class="accessory_line ui-helper-clearfix">
				<div class="accessory_title"><?php echo html::escapeHTML($aAccessory['title']) ?></div>
				<div class="accessory_quantity"><?php echo html::escapeHTML($aAccessory['quantity']) ?></div>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>
	<?php endforeach; ?>


<h3><?php _e('m_estimate_details_comment') ?></h3>

	<p><?php echo util::nlToP(html::escapeHTML($rsEstimate->content['comment'])) ?></p>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
