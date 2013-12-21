<?php
/**
 * @ingroup okt_module_guestbook
 * @brief La page de modification d'une signature.
 *
 */

use Tao\Admin\Page;
use Tao\Forms\Statics\FormElements as form;


# Accès direct interdit
if (!defined('ON_MODULE')) die;

if (!empty($_REQUEST['id'])){
	$sig_id = intval($_REQUEST['id']);
}
else {
	http::redirect('index.php?'.$url_params);
}

# récupération des infos de la signature
$signature = $okt->guestbook->getSig(array('id'=>$sig_id));

$aSigData = array(
	'id' 		=> $sig_id,
	'language'  => $signature->language,
	'message' 	=> $signature->message,
	'nom' 		=> $signature->nom,
	'email' 	=> $signature->email,
	'url' 		=> $signature->url,
	'note' 		=> $signature->note,
	'visible' 	=> $signature->visible,
	'date' 		=> dt::dt2str(__('%A, %B %d, %Y, %H:%M'), $signature->date_sign)
);


# formulaire envoyé
if (!empty($_POST['edit_form_sent']))
{
	$aSigData = array(
		'id' 		=> $sig_id,
		'language'  => isset($_POST['language']) ? $_POST['language'] : null,
		'message' 	=> isset($_POST['msg']) ? $_POST['msg'] : null,
		'nom' 		=> isset($_POST['nom']) ? $_POST['nom'] : null,
		'email' 	=> isset($_POST['email']) ? $_POST['email'] : null,
		'url' 		=> isset($_POST['url']) ? $_POST['url'] : null,
		'note' 		=> isset($_POST['note']) ? intval($_POST['note']) : null,
		'visible' 	=> isset($_POST['visible']) ? intval($_POST['visible']) : 1,
		'date' 		=> dt::dt2str(__('%A, %B %d, %Y, %H:%M'), $signature->date_sign)
	);

	$aSigData = $okt->guestbook->handleUserData($aSigData);

	if (!$okt->error->hasError())
	{
		if ($okt->guestbook->updSig($aSigData))
		{
			$okt->page->flashMessages->addSuccess(__('m_guestbook_Signature_was_updated'));

			http::redirect('module.php?m=guestbook&amp;action=edit&id='.$sig_id . $url_params);
		}
	}
}


/* Affichage
----------------------------------------------------------*/

# Titre de la page
$okt->page->addGlobalTitle(__('m_guestbook_Edit_a_signature'));


# En-tête
include OKT_ADMIN_HEADER_FILE; ?>

<form action="module.php" method="post">
	<fieldset>
		<legend><?php _e('m_guestbook_Seize_informations_about_signature') ?></legend>

		<?php # début champ langue
		if($okt->guestbook->config->chp_language) : ?>

		<p class="field"><label for="language" <?php if ($okt->guestbook->config->chp_language == 2) echo ' class="required" title="'.__('c_c_required_field').'"'; ?>><?php _e('m_guestbook_language') ?></label>
		<?php echo form::select('language',$aLanguagesList, $aSigData['language']) ?></p>

		<?php endif;
		# fin champ langue ?>

	<?php if ($okt->guestbook->config->chp_nom) : ?>
		<p class="field"><label for="nom"><?php _e('m_guestbook_full_name')?></label>
		<?php echo form::text('nom', 60, 255, html::escapeHTML($aSigData['nom'])); ?></p>
	<?php endif; ?>

	<?php if ($okt->guestbook->config->chp_mail) : ?>
		<p class="field"><label for="email"><?php _e('m_guestbook_email')?></label>
		<?php echo form::text('email', 60, 255, html::escapeHTML($aSigData['email'])); ?></p>
	<?php endif; ?>

	<?php if ($okt->guestbook->config->chp_url) : ?>
		<p class="field"><label for="url"><abbr title="Uniform Resource Locator" lang="en">URL</abbr> site</label>
		<?php echo form::text('url', 60, 255, html::escapeHTML($aSigData['url'])); ?></p>
	<?php endif; ?>

		<p class="field"><span class="fake-label"><?php _e('c_c_Date')?> :</span>
		<span class="fake-input"><?php echo $aSigData['date']; ?></span></p>

	<?php if ($okt->guestbook->config->chp_note) : ?>
		<p class="field"><label for="note"><?php _e('m_guestbook_note')?></label>
		<?php echo form::text('note', 3, 2, ceil($aSigData['note'])); ?>/20</p>
	<?php endif; ?>

		<p><label for="msg"><?php _e('m_guestbook_message')?></label></p>
		<p><?php echo form::textarea('msg', 57, 10, html::escapeHTML($aSigData['message'])); ?></p>

	<?php if ($okt->guestbook->config->validation) : ?>
		<p class="field"><span class="fake-label"><?php _e('m_guestbook_Valid') ?></span>
		<label><input type="radio" name="visible" id="visible" value="1"  <?php if ($aSigData['visible'] == 1) echo "checked" ?> /> Oui</label>
		<label><input type="radio" name="visible" id="visible" value="0" <?php if ($aSigData['visible'] == 0) echo "checked" ?> /> Non</label>
		</p>
	<?php endif; ?>
	</fieldset>

	<p><?php echo form::hidden('m', 'guestbook'); ?>
	<?php echo form::hidden('action','edit'); ?>
	<?php echo form::hidden('page',$page); ?>
	<?php echo form::hidden('show',$show); ?>
	<?php echo form::hidden('status',$status); ?>
	<?php echo form::hidden('id',$sig_id); ?>
	<?php echo form::hidden('edit_form_sent',1); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" name="modifier" value="<?php _e('c_c_action_Edit') ?>" />
	<a href="module.php?m=guestbook&amp;action=save&amp;do=supp&amp;id=<?php echo
	$sig_id; ?><?php echo $url_params ?>" onclick="return window.confirm('<?php
	_e('m_guestbook_Confirm_signature_deletion') ?>')" class="icon cross"><?php
	_e('c_c_action_Delete')?></a>
	<a href="module.php?m=guestbook&amp;action=index<?php echo $url_params ?>" class="icon arrow_turn_left"><?php
	_e('c_c_action_Go_back')?></a></p>

</form>

<?php # Pied-de-page
include OKT_ADMIN_FOOTER_FILE; ?>
