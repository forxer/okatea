<?php
/**
 * @ingroup okt_module_partners
 * @brief Ajout ou modification d'un partenaire internationalisé
 *
 */

use Tao\Admin\Page;
use Tao\Utils as util;
use Tao\Forms\StaticFormElements as form;

# Accès direct interdit
if (!defined('ON_PARTNERS_MODULE')) die;


/* Initialisations
----------------------------------------------------------*/

$partner_id = null;

$p_name = '';
$p_description = array();
$p_url = array();
$p_url_title = array();

foreach ($okt->languages->list as $aLanguage)
{
	$p_description[$aLanguage['code']] = '';
	$p_url[$aLanguage['code']] = '';
	$p_url_title[$aLanguage['code']] = '';
}

$p_active = 1;

$p_date = '';
$p_heure = '';
$p_minute = '';

$p_updated_at = '';
$p_created_at = '';
$p_category_id = '';

# update partner ?
if (!empty($_REQUEST['partner_id']))
{
	$partner_id = intval($_REQUEST['partner_id']);
	$partners_infos = $okt->partners->getPartners(array('id'=>$partner_id,'active'=>2));
	$partners_inter = $okt->partners->getPartnerInter($partner_id);

	foreach ($okt->languages->list as $aLanguage)
	{
		$p_description[$aLanguage['code']] = '';
		$p_url[$aLanguage['code']] = '';
		$p_url_title[$aLanguage['code']] = '';

		while ($partners_inter->fetch())
		{
			if ($partners_inter->language == $aLanguage['code'])
			{
				$p_description[$partners_inter->language] = $partners_inter->description;
				$p_url[$partners_inter->language] = $partners_inter->url;
				$p_url_title[$partners_inter->language] = $partners_inter->url_title;
			}
		}
	}

	$p_active = $partners_infos->active;
	$p_name = $partners_infos->name;
	$p_category_id = $partners_infos->category_id;
	$ts = strtotime($partners_infos->created_at);
	$p_date = date('d-m-Y',$ts);
	$p_heure = date('H',$ts);
	$p_minute = date('i',$ts);

	$p_updated_at = $partners_infos->updated_at;
	$p_created_at = $partners_infos->created_at;

	if ($okt->partners->config->images['enable']) {
		$aPartnerLogoInfos = $partners_infos->getImagesInfo();
	}
}

# Récupération de la liste complète des catégorties
$rsCategories = $okt->partners->getCategories(array('visibility'=>2, 'language' => $okt->user->language));


/* Traitements
----------------------------------------------------------*/

# Switch partenaire statut
if (!empty($_GET['switch_status']) && !empty($partner_id))
{
	$okt->partners->setPartnerStatus($partner_id);
	http::redirect('module.php?m=partners&action=edit&partner_id='.$partner_id.'&switched=1');
}

# Suppression d'une image
if (!empty($_GET['delete_image']) && !empty($partner_id))
{
	$okt->partners->deleteLogo($partner_id, 1);
	http::redirect('module.php?m=partners&action=edit&partner_id='.$partner_id.'&edited=1');
}

# Formulaire envoyé
if (!empty($_POST['sended']))
{
	$p_active = !empty($_POST['p_active']) ? 1 : 0;
	$p_category_id = !empty($_POST['p_category_id']) ? $_POST['p_category_id'] : null;
	$p_name = !empty($_POST['p_name']) ? $_POST['p_name'] : null;
	$p_description = !empty($_POST['p_description']) ? array_map('trim',$_POST['p_description']) : array();
	$p_url = !empty($_POST['p_url']) ? array_map('trim',$_POST['p_url']) : array();
	$p_url_title = !empty($_POST['p_url_title']) ? array_map('trim',$_POST['p_url_title']) : array();

	$date = trim($p_date.' '.(!empty($p_heure) && !empty($p_minute) ? $p_heure.':'.$p_minute : ''));
	$params = array(
		'active' => $p_active,
		'category_id' => $p_category_id,
		'name' => $p_name,
		'descriptions' => $p_description,
		'urls' => $p_url,
		'urls_titles' => $p_url_title,
		'date' => $date
	);

	# update partner
	if (!empty($partner_id))
	{
		$params['id'] = $partner_id;

		$okt->partners->updPartner($params);

		$okt->page->flashMessages->addSuccess(__('m_partners_updated'));

		http::redirect('module.php?m=partners&action=edit&partner_id='.$partner_id);
	}

	# add partner
	else
	{
		$partner_id = $okt->partners->addPartner($params);

		$okt->page->flashMessages->addSuccess(__('m_partners_added'));

		http::redirect('module.php?m=partners&action=edit&partner_id='.$partner_id);
	}
}


# Réglages de la validation javascript
$aValidateFieldsJs = array();

if ($okt->partners->config->chp_name) {
	$aValidateFieldsJs[] = array(
		'id' => 'p_name',
		'rules' => 'required: '.($okt->partners->config->chp_name == 2 ? 'true' : 'false')
	);
}

if ($okt->partners->config->chp_description) {
	$aValidateFieldsJs[] = array(
		'id' => '\'p_description['.$okt->config->language.']\'',
		'rules' => 'required: '.($okt->partners->config->chp_description == 2 ? 'true' : 'false')
	);
}

if ($okt->partners->config->chp_url) {
	$aValidateFieldsJs[] = array(
		'id' => '\'p_url['.$okt->config->language.']\'',
		'rules' => 'required: '.($okt->partners->config->chp_url == 2 ? 'true' : 'false')
	);
}

if ($okt->partners->config->chp_url_title) {
	$aValidateFieldsJs[] = array(
		'id' => '\'p_url_title['.$okt->config->language.']\'',
		'rules' => 'required: '.($okt->partners->config->chp_url_title == 2 ? 'true' : 'false')
	);
}

# Validation javascript
$okt->page->validate('partners-form',$aValidateFieldsJs);



/* Affichage
----------------------------------------------------------*/

# bouton retour
$okt->page->addButton('partnersBtSt',array(
	'permission' 	=> true,
	'title' 		=> __('c_c_action_Go_back'),
	'url' 			=> 'module.php?m=partners&amp;action=index',
	'ui-icon' 		=> 'arrowreturnthick-1-w',
),'before');

# add partner
if (empty($partner_id)) {
	$okt->page->addGlobalTitle(__('m_partners_add_partner'));
}
else
{
	$okt->page->addGlobalTitle(__('m_partners_edit_partner'));

	# Buttons
	$okt->page->addButton('partnersBtSt',array(
		'permission' 	=> true,
		'title' 		=> ($p_active ? __('c_c_status_Online') : __('c_c_status_Offline')),
		'url' 			=> 'module.php?m=partners&amp;action=edit&amp;partner_id='.$partner_id.'&amp;switch_status=1',
		'ui-icon' 		=> ($p_active ? 'volume-on' : 'volume-off'),
		'active' 		=> $p_active,
	));
	$okt->page->addButton('partnersBtSt',array(
		'permission' 	=> $okt->checkPerm('partners_remove'),
		'title' 		=> __('c_c_action_delete'),
		'url' 			=> 'module.php?m=partners&amp;action=delete&amp;partner_id='.$partner_id,
		'ui-icon' 		=> 'closethick',
		'onclick' 		=> 'return window.confirm(\''.html::escapeJS(__('m_partners_confirm_delete')).'\')',
	));
}


# Tabs
$okt->page->tabs();

# Modal
$okt->page->applyLbl($okt->partners->config->lightbox_type);

# RTE
$okt->page->applyRte($okt->partners->config->enable_rte,'textarea.richTextEditor');

# Lang switcher
if (!$okt->languages->unique) {
	$okt->page->langSwitcher('#tabered','.lang-switcher-buttons');
}


# En-tête
include OKT_ADMIN_HEADER_FILE; ?>

<?php echo $okt->page->getButtonSet('partnersBtSt'); ?>

<form action="module.php" method="post" id="partners-form" enctype="multipart/form-data">
	<div id="tabered">
		<ul>
			<li><a href="#tab-add-partner"><?php _e('m_partners_general') ?></a></li>
			<?php if($okt->partners->config->chp_logo > 0) : ?>
			<li><a href="#tab-logo"><?php _e('m_partners_logo')?></a></li>
			<?php endif; ?>
		</ul>
		<div id="tab-add-partner">
			<?php if($okt->partners->config->chp_name > 0) : ?>
			<p class="field"><label for="p_name" <?php if($okt->partners->config->chp_name == 2) :?>class="required" <?php endif;?>><?php _e('c_c_Name') ?></label>
			<?php echo form::text('p_name', 40, 255, html::escapeHTML($p_name)) ?></p>
			<?php endif; ?>

			<?php foreach ($okt->languages->list as $aLanguage) : ?>

				<?php if($okt->partners->config->chp_description > 0) :?>
					<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_description_<?php echo $aLanguage['code'] ?>" <?php if($okt->partners->config->chp_description == 2 && $aLanguage['code'] == $okt->user->language) :?>class="required" <?php endif;?>><?php _e('c_c_Description')?><span class="lang-switcher-buttons"></span></label>
					<?php echo form::textarea(array('p_description['.$aLanguage['code'].']','p_description_'.$aLanguage['code']), 57, 10, $p_description[$aLanguage['code']],'richTextEditor') ?></p>
				<?php endif; ?>

				<?php if($okt->partners->config->chp_url > 0 ) :?>
					<p class="field" lang="<?php echo $aLanguage['code']?>"><label for="p_url_<?php echo $aLanguage['code'] ?>" <?php if($okt->partners->config->chp_url == 2 && $aLanguage['code'] == $okt->user->language) :?>class="required" <?php endif;?>><?php _e('m_partners_website')?><span class="lang-switcher-buttons"></span></label>
					<?php echo form::text(array('p_url['.$aLanguage['code'].']','p_url_'.$aLanguage['code']), 60, 255, html::escapeHTML($p_url[$aLanguage['code']])) ?>
					<span class="note"><?php _e('m_partners_url_example') ?></span></p>
				<?php endif; ?>

				<?php if($okt->partners->config->chp_url_title > 0 ) :?>
					<p class="field" lang="<?php echo $aLanguage['code'] ?>"><label for="p_url_title_<?php echo $aLanguage['code'] ?>" <?php if($okt->partners->config->chp_url_title == 2 && $aLanguage['code'] == $okt->user->language) :?>class="required" <?php endif;?>><?php _e('m_partners_website_title')?><span class="lang-switcher-buttons"></span></label>
					<?php echo form::text(array('p_url_title['.$aLanguage['code'].']','p_url_title_'.$aLanguage['code']), 60, 255, html::escapeHTML($p_url_title[$aLanguage['code']])) ?></p>
				<?php endif; ?>

			<?php endforeach;?>

			<div class="two-cols">
				<p class="field col"><label><?php echo form::checkbox('p_active', 1, $p_active) ?> <?php _e('c_c_status_online') ?></label></p>
				<?php if($okt->partners->config->enable_categories) : ?>
				<p class="field col"><label for="p_category_id"><?php _e('m_partners_Category')?></label>
				<select id="p_category_id" name="p_category_id">
					<option value="0"></option>
					<?php
					while ($rsCategories->fetch())
					{
						echo '<option value="'.$rsCategories->id.'"'.
						($p_category_id == $rsCategories->id ? ' selected="selected"' : '').
						'>'.str_repeat('&nbsp;&nbsp;',$rsCategories->level).
						'&bull; '.html::escapeHTML($rsCategories->name).
						'</option>';
					}
					?>
				</select></p>
			<?php endif;?>
			</div>
		</div><!-- #tab-add-partner -->

		<?php if($okt->partners->config->chp_logo > 0) : ?>
			<div id="tab-logo">
				<div class="two-cols modal-box">
					<div class="col">

						<p class="field"><label for="p_images_1"><?php printf(__('m_partners_logo')) ?></label>
						<?php echo form::file('p_images_1') ?></p>

						<?php # il y a une image ?
						if (!empty($aPartnerLogoInfos)) :

							# affichage square ou icon ?
							if (isset($aPartnerLogoInfos['square_url'])) {
								$logo_url = $aPartnerLogoInfos['square_url'];
								$logo_attr = $aPartnerLogoInfos['square_attr'];
							}
							else {
								$logo_url = OKT_PUBLIC_URL.'/img/media/image.png';
								$logo_attr = ' width="48" height="48" ';
							}

							$logo_alt = isset($aPartnerLogoInfos['alt']) ? $aPartnerLogoInfos['alt'] : '';

							?>

							<p class="field"><label for="p_images_alt_1"><?php printf(__('m_partners_alternative_text')) ?></label>
							<?php echo form::text('p_images_alt_1',40,255,html::escapeHTML($logo_alt)) ?></p>

							<p><a href="<?php echo $aPartnerLogoInfos['img_url']?>" rel="partner_logo"
							title="<?php echo html::escapeHTML($logo_alt) ?>" class="modal"><img src="<?php echo $logo_url ?>"
							<?php echo $logo_attr ?> alt="<?php echo html::escapeHTML($logo_alt) ?>" /></a></p>

							<p><a href="module.php?m=partners&amp;action=edit&amp;partner_id=<?php
							echo $partner_id ?>&amp;delete_image=1"
							class="icon delete"><?php _e('m_partners_delete_logo')?></a></p>

						<?php else : ?>

							<p class="field"><label for="p_images_alt_1"><?php printf(__('m_partners_alternative_text')) ?></label>
							<?php echo form::text('p_images_alt_1',40,255,'') ?></p>
						<?php endif; ?>
					</div>
				</div>
				<p class="note"><?php printf(__('c_c_maximum_file_size_%s'),util::l10nFileSize(OKT_MAX_UPLOAD_SIZE)) ?></p>
			</div><!-- #tab-logo -->
		<?php endif; ?>

	</div><!-- #tabered -->

	<p><?php echo form::hidden('m','partners'); ?>
	<?php echo form::hidden('action',!empty($partner_id) ? 'edit' : 'add'); ?>
	<?php echo !empty($partner_id) ? form::hidden('partner_id',$partner_id) : ''; ?>
	<?php echo form::hidden('sended',1); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php echo !empty($partner_id) ? _e('c_c_action_edit') : _e('c_c_action_add'); ?>" /></p>
</form>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>

