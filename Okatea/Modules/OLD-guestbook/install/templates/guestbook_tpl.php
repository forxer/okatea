<?php
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Html\Modifiers;

?>

<?php
# début Okatea : ce template étend le layout
$view->extend('Layout');
# fin Okatea : ce template étend le layout ?>


<?php
# début Okatea : ajout de la CSS spécifique au module
$okt->page->css->addFile($okt->theme->url . '/modules/guestbook/styles.css');
# fin Okatea : ajout de la CSS spécifique au module ?>


<?php
# début Okatea : ajout de jQuery
$okt->page->js->addFile($okt['public_url'] . '/components/jquery/dist/jquery.min.js');
# fin Okatea : ajout de jQuery ?>


<?php
# début Okatea : champs requis pour la validation JS
$aJsValidateRules = new ArrayObject();

if ($okt->guestbook->config->chp_language == 2)
{
	$aJsValidateRules[] = 'language: { required: true }';
}

if ($okt->guestbook->config->chp_nom == 2)
{
	$aJsValidateRules[] = 'nom: { required: true }';
}

if ($okt->guestbook->config->chp_mail == 2)
{
	$aJsValidateRules[] = 'email: { required: true }';
}

if ($okt->guestbook->config->chp_url == 2)
{
	$aJsValidateRules[] = 'url: { required: true }';
}

$aJsValidateRules[] = 'msg: { required: true }';
# fin Okatea : champs requis pour la validation JS ?>


<?php
# -- CORE TRIGGER : publicModuleGuestbookJsValidateRules
$okt['triggers']->callTrigger('publicModuleGuestbookJsValidateRules', $aJsValidateRules, $okt->guestbook->config->captcha);
?>



<?php
# début Okatea : validation JS
if (! empty($aJsValidateRules))
{
	$okt->page->validateForm();
	$okt->page->js->addReady("
		var contactValidator = $('#guestbook-form').validate({
			rules: {
				" . implode(',', (array) $aJsValidateRules) . "
			}
		});
	");
}
# fin Okatea : validation JS ?>


<?php
# début Okatea : ajout jQuery UI
$okt->page->js->addFile($okt['public_url'] . '/components/jquery-ui/ui/minified/jquery-ui.min.js');
$okt->page->css->addFile($okt['public_url'] . '/components/jquery-ui/themes/' . $okt['config']->jquery_ui['public'] . '/jquery-ui.min.css');
# fin Okatea : ajout jQuery UI ?>


<?php
# début Okatea : jQuery UI dialog pour le formulaire d'ajout
$okt->page->js->addReady('
	$(function() {

		$("#guestbook-add").dialog({
			autoOpen: ' . ($okt->error->hasError() ? 'true' : 'false') . '
			,hide: "fade"
			,show: "fade"
			,title: "' . __('m_guestbook_sign_it') . '"
			,width: 500
			,height: 350
		});

		$("#guestbook-add h3").hide();

		$("#guestbook-form-control>a").click(function(e){
			$("#guestbook-add").dialog("open");
			e.preventDefault();
		});
	});
');
# début Okatea : jQuery UI dialog pour le formulaire d'ajout ?>


<?php
# debut : affichage du message de confirmation d'ajout d'une signature
if (! empty($_GET['added']))
:
	?>

<div class="success_box">
	<p><?php _e('m_guestbook_thank_you') ?></p>

	<?php
/* message si la validation avant publication est activée */
	if ($okt->guestbook->config->validation)
	:
		?>
	<p><?php _e('m_guestbook_administrator_verify') ?></p>
	<?php endif; ?>
</div>


<?php endif;
# fin : affichage du message de confirmation d'ajout d'une signature ?>


<p id="guestbook-form-control">
	<a href="#guestbook-add" class="sign-link"><?php _e('m_guestbook_sign_it') ?></a>
</p>

<?php # debut : formulaire d'ajout de signature ?>
<div id="guestbook-add">
	<h3><?php _e('m_guestbook_add') ?></h3>

	<?php
# debut : affichage des éventuelles erreurs
	if ($okt->error->notEmpty())
	:
		?>

	<div class="errors_box"><?php echo $okt->error->get(); ?></div>

	<?php endif; # fin : affichage des éventuelles erreurs ?>


	<form
		action="<?php echo $view->escape(GuestbookHelpers::getGuestbookUrl()) ?>"
		id="guestbook-form" method="post">

		<p><?php _e('m_guestbook_thank_you_filling_form') ?></p>

		<?php
# début champ langue
		if (! $okt['languages']->unique && $okt->guestbook->config->chp_language)
		:
			?>

		<p class="field">
			<label for="language"
				<?php if ($okt->guestbook->config->chp_language == 2) echo ' class="required" title="'.__('c_c_required_field').'"'; ?>><?php _e('m_guestbook_language') ?></label>
		<?php echo form::select('language',$aLanguages,$aSigData['language']) ?></p>

		<?php endif; # fin champ langue ?>


		<?php
# début : champ nom
		if ($okt->guestbook->config->chp_nom)
		:
			?>

		<p class="field">
			<label for="nom"
				<?php if ($okt->guestbook->config->chp_nom == 2) echo ' class="required" title="'.__('c_c_required_field').'"'; ?>><?php _e('m_guestbook_full_name') ?></label>
			<input name="nom" type="text" id="nom" size="40"
				value="<?php echo $view->escape($aSigData['nom']) ?>" />
		</p>

		<?php endif; # fin : champ nom ?>


		<?php
# début : champ email
		if ($okt->guestbook->config->chp_mail)
		:
			?>

		<p class="field">
			<label for="email"
				<?php if ($okt->guestbook->config->chp_mail == 2) echo ' class="required" title="'.__('c_c_required_field').'"'; ?>><?php _e('m_guestbook_email') ?></label>
			<input type="text" name="email" id="email" size="40"
				value="<?php echo $view->escape($aSigData['email']) ?>" />
		</p>

		<?php endif; # fin : champ email ?>


		<?php
# début : champ URL
		if ($okt->guestbook->config->chp_url)
		:
			?>

		<p class="field">
			<label for="url"
				<?php if ($okt->guestbook->config->chp_url == 2) echo ' class="required" title="'.__('c_c_required_field').'"'; ?>><?php _e('m_guestbook_url') ?></label>
			<input type="text" name="url" id="url" size="60"
				value="<?php echo $view->escape($aSigData['url']) ?>" />
		</p>

		<?php endif; # fin : champ URL ?>


		<?php
# début : champ note
		if ($okt->guestbook->config->chp_note)
		:
			?>

		<p class="field">
			<label for="note"><?php _e('m_guestbook_note') ?></label> <select
				name="note" id="note" size="1">
				<option value="nc"
					<?php if ($aSigData['note'] == 'nc') { echo ' selected="selected"'; } ?>>-NC-</option>
			<?php

for ($i = 0; $i <= 20; $i ++)
			{
				echo '<option value="' . $i . '"' . ($aSigData['note'] != 'nc' && $aSigData['note'] == $i ? ' selected="selected"' : '') . '>' . $i . '/20</option>' . PHP_EOL;
			}
			?>
		</select>
		</p>

		<?php endif; # fin : champ note ?>


		<?php # début : champ message ?>

		<p class="field">
			<label for="msg" class="required"
				title="<?php _e('c_c_required_field') ?>"><?php _e('m_guestbook_message') ?></label>
		</p>
		<p>
			<textarea name="msg" id="msg" cols="40" rows="10"><?php echo $view->escape($aSigData['message']) ?></textarea>
		</p>

		<?php # fin : champ message ?>


		<?php
# -- CORE TRIGGER : publicModuleGuestbookTplFormBottom
		$okt['triggers']->callTrigger('publicModuleGuestbookTplFormBottom', $okt->guestbook->config->captcha);
		?>


		<p>
			<input name="sign" type="hidden" id="sign" value="1" /> <input
				name="valbout" type="submit"
				value="<?php _e('m_guestbook_submit') ?>" />
		</p>

	</form>
</div>
<!-- #guestbook-add -->
<?php # fin : formulaire d'ajout de signature ?>


<div class="signatures-list">
<?php
# debut : boucle sur les signatures à afficher
while ($signaturesList->fetch())
:
	?>

<div class="signature">

		<h3 class="title">
	<?php if ($okt->guestbook->config->chp_nom && $signaturesList->nom != '') : ?>
		<?php echo $view->escape($signaturesList->nom)?>
	<?php else : ?>
		<?php printf(__('m_guestbook_sign_num_%s'),$signaturesList->number)?>
	<?php endif; ?>
	</h3>

		<ul class="infos">
		<?php /* date */ ?>
		<li class="date"><?php printf(__('m_guestbook_on_%s'), dt::dt2str(__('%A, %B %d, %Y, %H:%M'), $signaturesList->date_sign)) ?></li>

		<?php
/* email */
	if ($okt->guestbook->config->chp_mail && $signaturesList->email != '')
	:
		?>
		<li class="email"><a
				href="mailto:<?php echo $view->escapeHtmlAttr(Modifiers::emailEncode($signaturesList->email)) ?>"><img
					src="<?php echo $okt->theme->url ?>/modules/guestbook/email.png"
					alt="<?php echo $view->escapeHtmlAttr(__('m_guestbook_email')) ?>" /></a></li>
		<?php endif; ?>

		<?php
/* URL */
	if ($okt->guestbook->config->chp_url && ($signaturesList->url != '' && $signaturesList->url != 'http://'))
	:
		?>
		<li class="url"><a
				href="<?php echo $view->escapeHtmlAttr($signaturesList->url) ?>"><img
					src="<?php echo $okt->theme->url ?>/modules/guestbook/house.png"
					alt="<?php echo $view->escapeHtmlAttr(__('m_guestbook_website')) ?>" /></a></li>
		<?php endif; ?>

		<?php
/* note */
	if ($signaturesList->note)
	:
		?>
		<li class="note"><?php _e('m_guestbook_note_colon') ?>&nbsp;<?php echo $signaturesList->note ?></li>
		<?php endif; ?>
	</ul>

		<p class="msg"><?php echo Modifiers::nlToP($view->escape($signaturesList->message)) ?></p>

	</div>
	<!-- .signature -->
<?php

endwhile
;
# fin : boucle sur les signatures à afficher ?>
</div>
<!-- #signatures-list -->


<?php
# debut : affichage pagination
if ($signaturesList->numPages > 1)
:
	?>
<ul class="pagination">
	<?php echo $signaturesList->pager->getLinks(); ?>
</ul>

<?php endif;
# fin : affichage pagination ?>

<?php
# début affichage liens vers les autres langues
if (! $okt['languages']->unique)
:
	?>
<p><?php

foreach ($aLanguages as $sLanguage => $sCode)
	:
		if ($okt['visitor']->language != $sCode)
		:
			?>
<a
		href="<?php echo $view->escapeHtmlAttr(GuestbookHelpers::getGuestbookUrl($sCode)) ?>"><?php echo $view->escape($sLanguage) ?></a>
<?php endif;
endforeach; ?></p>
<?php endif; # Fin affichage liens vers les autres langues ?>
