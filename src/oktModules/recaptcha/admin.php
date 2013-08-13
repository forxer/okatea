<?php
/**
 * @ingroup okt_module_recaptcha
 * @brief La page d'administration.
 *
 */

# Accès direct interdit
if (!defined('ON_RECAPTCHA_MODULE')) die;

# Perm ?
if (!$okt->checkPerm('recaptcha_config')) {
	$okt->redirect(OKT_ADMIN_LOGIN_PAGE);
}

#on récupère les clés actuelles
$p_publickey = $okt->recaptcha->config->publickey;
$p_privatekey = $okt->recaptcha->config->privatekey;
$p_theme = $okt->recaptcha->config->theme;


/* Traitements
----------------------------------------------------------*/

# Configuration envoyée
if (!empty($_POST['config_send']))
{
	$p_publickey = !empty($_POST['p_publickey']) ? $_POST['p_publickey'] : '';
	$p_privatekey = !empty($_POST['p_privatekey']) ? $_POST['p_privatekey'] : '';
	$p_theme = !empty($_POST['p_theme']) ? $_POST['p_theme'] : 'clean';

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'publickey' => $p_publickey,
			'privatekey' => $p_privatekey,
			'theme' => $p_theme
		);

		try
		{
			$okt->recaptcha->config->write($new_conf);
			$okt->redirect('module.php?m=recaptcha&action=index&edited=1');
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

# Liste des thèmes
$aThemes = array(
	__('m_recaptcha_theme_red') => 'red',
	__('m_recaptcha_theme_white') => 'white',
	__('m_recaptcha_theme_black') => 'blackglass',
	__('m_recaptcha_theme_clean') => 'clean'
);


# Titre de la page
$okt->page->addGlobalTitle('reCaptcha');

# Confirmations
$okt->page->messages->success('edited',__('Les clés ont été éditées.'));

# En-tête
require OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">

	<div class="three-cols">
		<p class="field col"><label for="p_publickey"><?php _e('m_recaptcha_public_key') ?></label>
		<?php echo form::text('p_publickey', 45, 45, $p_publickey) ?></p>

		<p class="field col"><label for="p_privatekey"><?php _e('m_recaptcha_private_key') ?></label>
		<?php echo form::text('p_privatekey', 45, 45, $p_privatekey) ?></p>

		<p class="field col"><label for="p_theme"><?php _e('m_recaptcha_theme') ?></label>
		<?php echo form::select('p_theme', $aThemes, $p_theme) ?></p>
	</div>


	<p class="col"><?php echo form::hidden('m','recaptcha'); ?>
	<?php echo form::hidden(array('config_send'), 1); ?>
	<?php echo form::hidden(array('action'), 'index'); ?>
	<?php echo adminPage::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>

</form>

<div class="two-cols">
	<div class="col note">
		<p><?php _e('m_recaptcha_localhost_keys') ?></p>

		<dl>
			<dt><?php _e('m_recaptcha_public_key') ?></dt>
			<dd><code>6LfxU9ISAAAAAE-DXwF9C4MlzpHxSYJO0lVMeiP_</code></dd>

			<dt><?php _e('m_recaptcha_private_key') ?></dt>
			<dd><code>6LfxU9ISAAAAANvQGwaJ7sbZ5kVJZ0y_nO0g2BYU</code></dd>
		</dl>
	</div>

	<p class="col"><a href="https://www.google.com/recaptcha/admin/create"
	class="link_sprite ss_key_add"><?php echo (__('m_recaptcha_get_keys'))?></a></p>
</div>


<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
