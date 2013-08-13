<?php
/**
 * @ingroup okt_module_guestbook
 * @brief La page de la liste des signatures.
 *
 */


# Accès direct interdit
if (!defined('ON_GUESTBOOK_MODULE')) die;

$aLanguages = l10n::getISOcodes();

$do = !empty($_REQUEST['do']) ? $_REQUEST['do'] : null;
$id = !empty($_REQUEST['id']) ? intval($_REQUEST['id']) : null;
$ids = array();
$multiple = false;


if (!empty($_REQUEST['sigs_ids']))
{
	foreach ($_REQUEST['sigs_ids'] as $k => $v) {
		$ids[$k] = (integer)$v;
	}

	$multiple = true;
}


# marquer comme étant du SPAM
if ($do == 'spam' && $okt->modules->moduleExists('antispam'))
{
	if ($multiple)
	{
		$ok = true;
		foreach ($ids as $cur_id) {
			$ok = $ok && $okt->guestbook->markSigAsSpam($cur_id);
		}

		if ($ok) {
			http::redirect('module.php?m=guestbook&amp;action=index'.$url_params.'&amp;updateds=1');
		}
	}
	elseif ($id) {
		if ($okt->guestbook->markSigAsSpam($id)) {
			http::redirect('module.php?m=guestbook&amp;action=index'.$url_params.'&amp;updated=1');
		}
	}
}

# marquer comme étant acceptable
elseif ($do == 'nospam')
{
	if ($multiple)
	{
		$ok = true;
		foreach ($ids as $cur_id) {
			$ok = $ok && $okt->guestbook->markSigAsNoSpam($cur_id);
		}

		if ($ok) {
			http::redirect('module.php?m=guestbook&amp;action=index'.$url_params.'&amp;updateds=1');
		}
	}
	elseif ($id) {
		if ($okt->guestbook->markSigAsNoSpam($id)) {
			http::redirect('module.php?m=guestbook&amp;action=index'.$url_params.'&amp;updated=1');
		}
	}
}

# validation signature
elseif ($do == 'valid')
{
	if ($multiple)
	{
		$ok = true;
		foreach ($ids as $cur_id) {
			$ok = $ok && $okt->guestbook->validateSig($cur_id);
		}

		if ($ok) {
			http::redirect('module.php?m=guestbook&amp;action=index'.$url_params.'&amp;validateds=1');
		}
	}
	elseif ($id) {
		if ($okt->guestbook->validateSig($id)) {
			http::redirect('module.php?m=guestbook&amp;action=index'.$url_params.'&amp;validated=1');
		}
	}
}

# suppression signature
elseif ($do == 'supp')
{
	if ($multiple)
	{
		$ok = true;
		foreach ($ids as $cur_id) {
			$ok = $ok && $okt->guestbook->delSig(array('id'=>$cur_id));
		}

		if ($ok) {
			http::redirect('module.php?m=guestbook&amp;action=index'.$url_params.'&amp;deleteds=1');
		}
	}
	elseif ($id) {
		if ($okt->guestbook->delSig(array('id'=>$id))) {
			http::redirect('module.php?m=guestbook&amp;action=index'.$url_params.'&amp;deleted=1');
		}
	}
}

# supprimer tous ce qui est considéré comme SPAM
if (!empty($_GET['delallspam']))
{
	if ($okt->guestbook->delSig(array('is_spam'=>true))) {
		http::redirect('module.php?m=guestbook&amp;action=index'.$url_params.'&amp;spamdeleted=1');
	}
}

$sigs_actions = array(
	'&nbsp;' => '',
	__('m_guestbook_Validate') => 'valid',
	__('c_c_action_Delete') => 'supp'
);

if ($okt->modules->moduleExists('antispam'))
{
	$sigs_actions[__('m_guestbook_Stand_out_as_unwanted')] = 'spam';
	$sigs_actions[__('m_guestbook_Stand_out_as_acceptable')] = 'nospam';
}


$params = array();

# afficher ?
switch ($show)
{
	# non-spam
	default:
	case 'nospam':
		$params['is_not_spam'] = true;
		break;

	# spam
	case 'spam':
		$params['is_spam'] = true;
		break;

	# toutes
	case 'all':
		break;
}

if(isset($language) && $language != 'all') {
	$params['language'] = $language;
}

# statut ?
switch($status)
{
	# validées
	case 'validated':
		$params['is_visible'] = true;
		break;

	# non-validées
	case 'not_validated':
		$params['is_not_visible'] = true;
		break;

	# toutes
	default:
	case 'all':
		break;
}

# pagination
$pager = new adminPager($page, $okt->guestbook->getSig($params,true), $okt->guestbook->config->nbparpage_admin);

# récupération des éléments
$params['limit'] = (($page-1)*$okt->guestbook->config->nbparpage_admin).','.$okt->guestbook->config->nbparpage_admin;
$signature = $okt->guestbook->getSig($params);

# nombre d’éléments au total
$nbsign = $okt->guestbook->getSig(array(),true);

# nombre d’éléments SPAM
$nbspam = $okt->guestbook->getSig(array('is_spam'=>true),true);


/* Affichage
----------------------------------------------------------*/

$okt->page->messages->success('updated',__('m_guestbook_Signature_was_updated'));
$okt->page->messages->success('updateds',__('m_guestbook_Signatures_were_updated'));
$okt->page->messages->success('validated',__('m_guestbook_Signature_was_validated'));
$okt->page->messages->success('validateds',__('m_guestbook_Signatures_were_validated'));
$okt->page->messages->success('deleted',__('m_guestbook_Signature_was_deleted'));
$okt->page->messages->success('deleteds',__('m_guestbook_Signatures_were_deleted'));
$okt->page->messages->success('spamdeleted',__('m_guestbook_SPAM_was_deleted'));

# En-tête
include OKT_ADMIN_HEADER_FILE; ?>

<p>Il y a un total de <?php echo $nbsign; ?> signature<?php if ($nbsign > 1) echo 's'; ?> dans votre livre d'or dont <?php echo $nbspam; ?> considérée<?php if ($nbspam > 1) echo 's'; ?> comme étant du SPAM.</p>

<form action="module.php" method="get">
	<p>Afficher les signatures <label for="show">de type</label>
	<?php echo form::select('show',$show_list,$show) ?>
	<label for="status">et de statut</label>
	<?php echo form::select('status',$status_list,$status) ?>
	<label for="language">dans la langue </label>
	<?php echo form::select('language',$aLanguagesList,$language) ?>
	<?php echo form::hidden('m', 'guestbook'); ?>
	<input type="hidden" name="action" value="index" />
	<input type="submit" value="<?php _e('c_c_action_Display')?>" /></p>
</form>

<?php if ($show != 'nospam' && $nbspam > 0) : ?>
	<p><a href="module.php?m=guestbook&amp;action=index&amp;delallspam=1&amp;<?php echo $url_params ?>"
	onclick="return window.confirm('Etes-vous sür de vouloir supprimer toutes les signatures marquées comme étant du SPAM ? Ceci est irréversible. Nous vous conseillons de vérifier qu\'une signature légitime ne s\'est pas glissée dans le SPAM avant de valider la suppression.')"
	class="link_sprite ss_delete"><?php _e('m_guestbook_Delete_SPAM')?></a></p>
<?php endif; ?>

<?php if ($signature->isEmpty()) : ?>
	<p><?php _e('m_guestbook_No_signatures_to_display')?></p>

<?php else : ?>

	<?php if ($pager->getNbPages() > 1) : ?>
	<ul class="pagination"><?php echo $pager->getLinks(); ?></ul>
	<?php endif; ?>

	<form action="module.php?m=guestbook" method="post" id="signatures">

	<?php # boucle sur les signatures
	while ($signature->fetch()) : ?>

<div class="signature-box">
	<h3 class="signature-title ui-widget-header ui-corner-top"><label>
		<?php echo form::checkbox('sigs_ids[]',$signature->id); ?> <?php
		if ($okt->guestbook->config->chp_nom && $signature->nom != '') {
			echo htmlspecialchars($signature->nom);
		}
		else {
			echo __('m_guestbook_Signature').' #'.$signature->id;
		}
		?></label></h3>

	<div class="ui-widget-content ui-corner-bottom">

		<div class="signature" id="sig-<?php echo $signature->id ?>">

		<?php
		$tmp_res = array();

		if ($okt->guestbook->config->chp_note)
		{
			if (!is_numeric($signature->note)) {
				$signature->note = 'n/a';
			}
			else {
				$signature->note = ceil($signature->note).'/20';
			}
			$tmp_res[] = __('m_guestbook_Note').': <strong><em>'.$signature->note.'</em></strong>';
		}

		if ($okt->guestbook->config->chp_language)
		{
			$signature->language = $aLanguages[$signature->language];

			$tmp_res[] = __('c_c_Language').' : <strong>'.$signature->language.'</strong>';
		}
		$tmp_res[] = '<em>Le '.strftime('%d/%m/%Y &agrave; %H:%M', strtotime($signature->date_sign)).'</em>';

		if ($okt->guestbook->config->chp_mail && $signature->email != '') {
			$tmp_res[] = '<a href="mailto:'.htmlspecialchars($signature->email).'">'.__('c_c_Email').'</a>';
		}

		if ($okt->guestbook->config->chp_url && $signature->url != '' && $signature->url != 'http://') {
			$tmp_res[] = '<a href="'.htmlspecialchars($signature->url).'">'.__('m_guestbook_Website').'</a>';
		}

		if (!empty($tmp_res)) {
			echo '<div class="signature-infos ui-widget-content ui-corner-all"><h4>'.__('m_guestbook_Infos').'</h4><ul><li>'.implode("</li><li>",$tmp_res).'</li></ul></div>';
		}

		echo '<p class="message">'.nl2br(htmlspecialchars($signature->message)).'</p>';

		?>
		</div><!-- .signature -->
		<div class="signature-actions">
			<ul>
				<li><a href="module.php?m=guestbook&amp;action=edit&amp;id=<?php echo $signature->id ?>&amp;<?php echo $url_params ?>"
				class="link_sprite ss_pencil"><?php _e('c_c_action_Edit')?></a></li>

			<?php if ($okt->guestbook->config->validation && $signature->visible == 0) : ?>
				<li><a href="module.php?m=guestbook&amp;action=index&amp;do=valid&amp;id=<?php echo $signature->id ?><?php echo $url_params ?>"
				onclick="return window.confirm('<?php _e('m_guestbook_Confirm_signature_validation') ?>')"
				class="link_sprite ss_tick"><?php _e('m_guestbook_Validate')?></a></li>
			<?php endif; ?>

				<li><a href="module.php?m=guestbook&amp;action=index&amp;do=supp&amp;id=<?php echo $signature->id ?><?php echo $url_params ?>"
				onclick="return window.confirm('<?php _e('m_guestbook_Confirm_signature_deletion') ?>')"
				class="link_sprite ss_cross"><?php _e('c_c_action_Delete')?></a></li>


		<?php if ($okt->modules->moduleExists('antispam')) : ?>
			<?php if (!$signature->spam_status) : ?>
				<li><a href="module.php?m=guestbook&amp;action=index&amp;do=spam&amp;id=<?php echo $signature->id ?><?php echo $url_params ?>"
				class="link_sprite ss_flag_red"><?php _e('m_guestbook_Stand_out_as_unwanted') ?></a></li>
			<?php else : ?>
				<li><a href="module.php?m=guestbook&amp;action=index&amp;do=nospam&amp;id=<?php echo $signature->id ?><?php echo $url_params ?>"
				class="link_sprite ss_flag_green"><?php _e('m_guestbook_Stand_out_as_acceptable') ?></a>
				<?php if ($okt->modules->moduleExists('antispam')) {
				echo oktAntispam::statusMessage($signature);
				} ?></li>
			<?php endif; ?>
		<?php endif; ?>

			</ul>
		</div><!-- .signature-actions -->
	</div>

</div><!-- .signature-box -->
	<?php endwhile; ?>

	<p class="right">
	<?php echo form::hidden('m', 'guestbook'); ?>
	<?php echo form::hidden('action','index') ?>
	<?php echo form::hidden('page',$page) ?>
	<?php echo form::hidden('show',$show) ?>
	<?php echo form::hidden('status',$status) ?>
	<?php echo adminPage::formtoken(); ?>
	<label>Action sur les signatures sélectionnées&nbsp;<?php
	echo form::select('do', $sigs_actions) ?>
	</label>&nbsp;<input type="submit" name="submit" value="ok" /></p>

	</form><!-- #signatures -->

	<?php # affichage pagination
	if ($pager->getNbPages() > 1) {
		echo '<ul class="pagination">'.$pager->getLinks().'</ul>';
	}

endif; ?>

<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
