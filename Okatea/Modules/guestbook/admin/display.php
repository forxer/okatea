<?php
/**
 * @ingroup okt_module_guestbook
 * @brief La page de configuration du module.
 *
 */

use Okatea\Admin\Page;
use Okatea\Tao\Forms\Statics\FormElements as form;


# Accès direct interdit
if (!defined('ON_MODULE')) die;

$p_nbparpage_admin = $okt->guestbook->config->nbparpage_admin;
$p_nbparpage_public = $okt->guestbook->config->nbparpage_public;


if (!empty($_POST['form_sent']))
{
	$p_nbparpage_admin = intval($_POST['p_nbparpage_admin']);
	$p_nbparpage_public = intval($_POST['p_nbparpage_public']);

	if (!preg_match('/^[0-9]+$/',$p_nbparpage_admin) || $p_nbparpage_admin < 1) {
		$okt->error->set(__('m_guestbook_valid_signature_number_for_admin'));
	}

	if (!preg_match('/^[0-9]+$/',$p_nbparpage_public) || $p_nbparpage_public < 1) {
		$okt->error->set(__('m_guestbook_valid_signature_number_for_public'));
	}

	if ($okt->error->isEmpty())
	{
		$aNewConf = array(
			'nbparpage_admin' => (integer)$p_nbparpage_admin,
			'nbparpage_public' => (integer)$p_nbparpage_public,
		);

		try
		{
			$okt->guestbook->config->write($aNewConf);

			$okt->page->flash->success(__('c_c_confirm_configuration_updated'));

			http::redirect('module.php?m=guestbook&action=display');
		}
		catch (InvalidArgumentException $e)
		{
			$okt->error->set(__('c_c_error_writing_configuration'));
			$okt->error->set($e->getMessage());
		}
	}
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_display'));

# Tabs
$okt->page->tabs();


# En-tête
include OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">
	<div id="tabered">
		<ul>
			<li><a href="#tab-numbers"><?php _e('m_guestbook_Number_by_page')?></a></li>
		</ul>

		<div id="tab-numbers">
			<fieldset>
				<legend><?php _e('m_guestbook_Number_of_signature_by_page')?></legend>
				<div class="three-cols">
					<p class="col field"><label for="p_nbparpage_admin"><?php _e('m_guestbook_On_administration_side')?></label>
					<?php echo form::text('p_nbparpage_admin', 3, 5, $p_nbparpage_admin) ?></p>

					<p class="col field"><label for="p_nbparpage_public"><?php _e('m_guestbook_On_public_side')?></label>
					<?php echo form::text('p_nbparpage_public', 3, 5, $p_nbparpage_public) ?></p>
				</div>
			</fieldset>
		</div><!-- #tab-numbers -->

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m', 'guestbook'); ?>
	<?php echo form::hidden('action', 'display'); ?>
	<?php echo form::hidden('form_sent', 1); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_Save') ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>

