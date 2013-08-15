<?php
/**
 * Configuration de base
 *
 * @addtogroup Okatea
 * @subpackage 		Install interface
 *
 */

if (!defined('OKT_INSTAL_PROCESS')) die;


/* Initialisations
------------------------------------------------------------*/

# Inclusion du prepend
require_once __DIR__.'/../../../oktInc/prepend.php';

# locales
l10n::set(OKT_INSTAL_DIR.'/inc/locales/'.$_SESSION['okt_install_language'].'/install');
l10n::set(OKT_LOCALES_PATH.'/'.$_SESSION['okt_install_language'].'/admin.site');
l10n::set(OKT_LOCALES_PATH.'/'.$_SESSION['okt_install_language'].'/admin.advanced');


$p_title = 'Okatea project';
$p_desc = 'Another CMS experience';

$p_company_name = '';
$p_company_com_name = '';
$p_company_siret = '';

$p_leader_name = '';
$p_leader_firstname = '';

$p_address_street = '';
$p_address_street_2 = '';
$p_address_code = '';
$p_address_city = '';
$p_address_country = '';
$p_address_tel = '';
$p_address_mobile = '';
$p_address_fax = '';

$p_courriel_address = 'contact@'.str_replace('www.','',$_SERVER['HTTP_HOST']);
$p_courriel_name = $p_title;

$p_title_tag = '';
$p_meta_description = '';
$p_meta_keywords = '';

$p_app_path = str_replace('install','',dirname($_SERVER['REQUEST_URI']));
$p_domain = $_SERVER['HTTP_HOST'];


/* Traitements
------------------------------------------------------------*/

if (!empty($_POST['sended']))
{
	$p_title = !empty($_POST['p_title']) ? $_POST['p_title'] : '';
	if (empty($p_title)) {
		$okt->error->set(__('c_a_config_pleaz_give_website_title'));
	}

	$p_desc = !empty($_POST['p_desc']) ? $_POST['p_desc'] : '';

	$p_company_name = !empty($_POST['p_company_name']) ? $_POST['p_company_name'] : '';
	$p_company_com_name = !empty($_POST['p_company_com_name']) ? $_POST['p_company_com_name'] : '';
	$p_company_siret = !empty($_POST['p_company_siret']) ? $_POST['p_company_siret'] : '';

	$p_leader_name = !empty($_POST['p_leader_name']) ? $_POST['p_leader_name'] : '';
	$p_leader_firstname = !empty($_POST['p_leader_firstname']) ? $_POST['p_leader_firstname'] : '';

	$p_address_street = !empty($_POST['p_address_street']) ? $_POST['p_address_street'] : '';
	$p_address_street_2 = !empty($_POST['p_address_street']) ? $_POST['p_address_street_2'] : '';
	$p_address_code = !empty($_POST['p_address_code']) ? $_POST['p_address_code'] : '';
	$p_address_city = !empty($_POST['p_address_city']) ? $_POST['p_address_city'] : '';
	$p_address_country = !empty($_POST['p_address_country']) ? $_POST['p_address_country'] : '';
	$p_address_tel = !empty($_POST['p_address_tel']) ? $_POST['p_address_tel'] : '';
	$p_address_mobile = !empty($_POST['p_address_mobile']) ? $_POST['p_address_mobile'] : '';
	$p_address_fax = !empty($_POST['p_address_fax']) ? $_POST['p_address_fax'] : '';

	$p_courriel_address = !empty($_POST['p_courriel_address']) ? $_POST['p_courriel_address'] : '';
	if ($p_courriel_address != '' && !text::isEmail($p_courriel_address)) {
		$okt->error->set(sprintf(__('c_c_error_invalid_email'),html::escapeHTML($p_courriel_address)));
	}

	$p_courriel_name = !empty($_POST['p_courriel_name']) ? $_POST['p_courriel_name'] : '';

	$p_title_tag = !empty($_POST['p_title_tag']) ? $_POST['p_title_tag'] : '';
	$p_meta_description = !empty($_POST['p_meta_description']) ? $_POST['p_meta_description'] : '';
	$p_meta_keywords = !empty($_POST['p_meta_keywords']) ? $_POST['p_meta_keywords'] : '';

	$p_app_path = !empty($_POST['p_app_path']) ? $_POST['p_app_path'] : '/';
	$p_app_path = util::formatAppPath($p_app_path);

	$p_domain = !empty($_POST['p_domain']) ? $_POST['p_domain'] : '';
	$p_domain = util::formatAppPath($p_domain,false,false);

	if ($okt->error->isEmpty())
	{
		$new_conf = array(
			'title' 			=> array('fr' => $p_title),
			'desc' 				=> array('fr' => $p_desc),
			'company' 			 => array(
				'name' 				=> $p_company_name,
				'com_name' 			=> $p_company_com_name,
				'siret' 			=> $p_company_siret
			),
			'address' 			 => array(
				'street' 			=> $p_address_street,
				'street_2' 			=> $p_address_street_2,
				'code' 				=> $p_address_code,
				'city' 				=> $p_address_city,
				'country'			=> $p_address_country,
				'tel'				=> $p_address_tel,
				'mobile'			=> $p_address_mobile,
				'fax'				=> $p_address_fax
			),
			'leader' 			 => array(
				'name' 				=> $p_leader_name,
				'firstname' 		=> $p_leader_firstname
			),
			'courriel_address' 	=> $p_courriel_address,
			'courriel_name' 	=> $p_courriel_name,
			'title_tag' 		=> array('fr' => $p_title_tag),
			'meta_description' 	=> array('fr' => $p_meta_description),
			'meta_keywords' 	=> array('fr' => $p_meta_keywords),
			'app_path' 			=> $p_app_path,
			'domain' 			=> $p_domain,
		);

		try
		{
			$_SESSION['okt_install_chemin'] = $new_conf['app_path'];

			$okt->config->write($new_conf);
			http::redirect('index.php?step='.$stepper->getNextStep());
		}
		catch (InvalidArgumentException $e)
		{
			$okt->error->set(__('c_c_error_writing_configuration'));
			$okt->error->set($e->getMessage());
		}
	}
}


/* Affichage
------------------------------------------------------------*/

$oHtmlPage->tabs();

# En-tête
$title = __('c_a_config_site');
require OKT_INSTAL_DIR.'/header.php'; ?>


<form action="index.php" method="post">
	<div id="tabered">
		<ul>
			<li><a href="#tab_general"><span><?php _e('c_a_config_tab_general') ?></span></a></li>
			<li><a href="#tab_company"><span><?php _e('c_a_config_tab_company') ?></span></a></li>
			<li><a href="#tab_email"><span><?php _e('c_a_config_tab_email') ?></span></a></li>
			<li><a href="#tab_seo"><span><?php _e('c_a_config_tab_seo') ?></span></a></li>
			<li><a href="#tab_advanced"><span><?php _e('c_a_config_advanced') ?></span></a></li>
		</ul>

		<div id="tab_general">
			<h3><?php _e('c_a_config_tab_general') ?></h3>

			<p class="field"><label for="p_title" title="<?php _e('c_c_required_field') ?>" class="required"><?php _e('c_a_config_website_title') ?></label>
			<?php echo form::text('p_title', 60, 255, html::escapeHTML($p_title)) ?></p>

			<p class="field"><label for="p_desc"><?php _e('c_a_config_website_desc') ?></label>
			<?php echo form::text('p_desc', 60, 255, html::escapeHTML($p_desc)) ?></p>
		</div><!-- #tab_general -->

		<div id="tab_company">
			<h3><?php _e('c_a_config_tab_company') ?></h3>

			<div class="two-cols">
				<div class="col">
					<fieldset>
						<legend><?php _e('c_a_config_company') ?></legend>

						<p class="field"><label for="p_company_name"><?php _e('c_a_config_company_name') ?></label>
						<?php echo form::text('p_company_name', 60, 255, html::escapeHTML($p_company_name)) ?></p>

						<p class="field"><label for="p_company_com_name"><?php _e('c_a_config_company_com_name') ?></label>
						<?php echo form::text('p_company_com_name', 60, 255, html::escapeHTML($p_company_com_name)) ?></p>

						<p class="field"><label for="p_company_siret"><?php _e('c_a_config_company_siret') ?></label>
						<?php echo form::text('p_company_siret', 60, 255, html::escapeHTML($p_company_siret)) ?></p>

					</fieldset>
				</div>
				<div class="col">
					<fieldset>
						<legend><?php _e('c_a_config_leader') ?></legend>

						<p class="field"><label for="p_leader_name"><?php _e('c_a_config_leader_name') ?></label>
						<?php echo form::text('p_leader_name', 60, 255, html::escapeHTML($p_leader_name)) ?></p>

						<p class="field"><label for="p_leader_firstname"><?php _e('c_a_config_leader_firstname') ?></label>
						<?php echo form::text('p_leader_firstname', 60, 255, html::escapeHTML($p_leader_firstname)) ?></p>
					</fieldset>
				</div>
			</div><!-- .two-cols -->

			<fieldset>
				<legend><?php _e('c_a_config_address') ?></legend>

				<div class="two-cols">
					<p class="field col"><label for="p_address_street"><?php _e('c_a_config_address_street') ?></label>
					<?php echo form::text('p_address_street', 60, 255, html::escapeHTML($p_address_street)) ?></p>

					<p class="field col"><label for="p_address_street_2"><?php _e('c_a_config_address_street_2') ?></label>
					<?php echo form::text('p_address_street_2', 60, 255, html::escapeHTML($p_address_street_2)) ?></p>
				</div>

				<div class="two-cols">
					<p class="field col"><label for="p_address_code"><?php _e('c_a_config_address_code') ?></label>
					<?php echo form::text('p_address_code', 10, 255, html::escapeHTML($p_address_code)) ?></p>

					<p class="field col"><label for="p_address_city"><?php _e('c_a_config_address_city') ?></label>
					<?php echo form::text('p_address_city', 60, 255, html::escapeHTML($p_address_city)) ?></p>
				</div>

				<div class="two-cols">

					<p class="field col"><label for="p_address_country"><?php _e('c_a_config_address_country') ?></label>
					<?php echo form::text('p_address_country', 60, 255, html::escapeHTML($p_address_country)) ?></p>
				</div>

				<div class="two-cols">
					<p class="field col"><label for="p_address_tel"><?php _e('c_a_config_address_tel') ?></label>
					<?php echo form::text('p_address_tel', 20, 255, html::escapeHTML($p_address_tel)) ?></p>

					<p class="field col"><label for="p_address_mobile"><?php _e('c_a_config_address_mobile') ?></label>
					<?php echo form::text('p_address_mobile', 20, 255, html::escapeHTML($p_address_mobile)) ?></p>
				</div>

				<div class="two-cols">
					<p class="field col"><label for="p_address_fax"><?php _e('c_a_config_address_fax') ?></label>
					<?php echo form::text('p_address_fax', 20, 255, html::escapeHTML($p_address_fax)) ?></p>
				</div>

			</fieldset>
		</div><!-- #tab_company -->

		<div id="tab_email">
			<h3><?php _e('c_a_config_tab_email') ?></h3>

			<fieldset>
				<legend><?php _e('c_a_config_sender') ?></legend>

				<p class="field"><label for="p_courriel_address"><?php _e('c_a_config_sender_address') ?></label>
				<?php echo form::text('p_courriel_address', 60, 255, html::escapeHTML($p_courriel_address)) ?></p>

				<p class="field"><label for="p_courriel_name"><?php _e('c_a_config_sender_name') ?></label>
				<?php echo form::text('p_courriel_name', 60, 255, html::escapeHTML($p_courriel_name)) ?></p>
			</fieldset>

		</div><!-- #tab_email -->

		<div id="tab_seo">
			<h3><?php _e('c_a_config_tab_seo') ?></h3>

			<p class="field"><label for="p_title_tag"><?php _e('c_a_config_title_tag') ?></label>
			<?php echo form::text('p_title_tag', 60, 255, html::escapeHTML($p_title_tag)) ?>
			<span class="note"><?php _e('c_a_config_title_tag_note') ?></span></p>

			<p class="field"><label for="p_meta_description"><?php _e('c_c_seo_meta_desc') ?></label>
			<?php echo form::text('p_meta_description', 60, 255, html::escapeHTML($p_meta_description)) ?></p>

			<p class="field"><label for="p_meta_keywords"><?php _e('c_c_seo_meta_keywords') ?></label>
			<?php echo form::textarea('p_meta_keywords', 57, 5, html::escapeHTML($p_meta_keywords)) ?></p>

		</div><!-- #tab_seo -->

		<div id="tab_advanced">
			<h3><?php _e('c_a_config_advanced') ?></h3>

			<p><label for="p_app_path"><?php printf(__('c_a_config_advanced_app_path'), http::getHost()) ?></label>
			<?php echo form::text('p_app_path', 40, 255, html::escapeHTML($p_app_path)) ?></p>

			<p class="field"><label for="p_domain"><?php _e('c_a_config_advanced_domain') ?></label>
			http://<?php echo form::text('p_domain', 60, 255, html::escapeHTML($p_domain)) ?></p>

		</div><!-- #tab_seo -->

	</div><!-- #tabered -->

	<p><input type="submit" value="<?php _e('c_c_next') ?>" />
	<input type="hidden" name="sended" value="1" />
	<input type="hidden" name="step" value="<?php echo $stepper->getCurrentStep() ?>" /></p>
</form>

<?php # Pied de page
require OKT_INSTAL_DIR.'/footer.php'; ?>
