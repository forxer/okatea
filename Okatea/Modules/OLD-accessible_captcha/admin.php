<?php
/**
 * @ingroup okt_module_accessible_captcha
 * @brief La page d'administration.
 *
 */
use Okatea\Admin\Page;
use Okatea\Tao\Forms\Statics\FormElements as form;

# Accès direct interdit
if (!defined('ON_MODULE'))
	die();
	
	# Perm ?
if (!$okt['visitor']->checkPerm('accessible_captcha_config'))
{
	http::redirect(OKT_ADMIN_LOGIN_PAGE);
}

# Les tableau ci-dessous contiendrons les textes localisés,
# une langue par ligne avec le code langue comme index
$aQuestions = [];
$aAnswers = [];

# Boucle sur la liste des langues disponibles
# afin d'initialiser ces tableaux
foreach ($okt['languages']->getList() as $aLanguage)
{
	$aQuestions[$aLanguage['code']] = '';
	$aAnswers[$aLanguage['code']] = '';
}

/* Traitements
----------------------------------------------------------*/

# Formulaire envoyé
if (!empty($_POST['manage_questions']))
{
	foreach ($okt['languages']->getList() as $aLanguage)
	{
		$aQuestions[$aLanguage['code']] = !empty($_POST['questions'][$aLanguage['code']]) ? $_POST['questions'][$aLanguage['code']] : [];
		$aAnswers[$aLanguage['code']] = !empty($_POST['reponses'][$aLanguage['code']]) ? $_POST['reponses'][$aLanguage['code']] : [];
		
		foreach ($aQuestions[$aLanguage['code']] as $cur_id => $data)
		{
			if (!empty($aQuestions[$aLanguage['code']][$cur_id]) && !empty($aAnswers[$aLanguage['code']][$cur_id]))
			{
				$okt->accessible_captcha->edit($cur_id, $aQuestions[$aLanguage['code']][$cur_id], $aAnswers[$aLanguage['code']][$cur_id]);
			}
			else
			{
				$okt->accessible_captcha->del($cur_id);
			}
		}
		
		if (!empty($_POST['question_add'][$aLanguage['code']]) && !empty($_POST['reponse_add'][$aLanguage['code']]))
		{
			$okt->accessible_captcha->add($_POST['question_add'][$aLanguage['code']], $_POST['reponse_add'][$aLanguage['code']], $aLanguage['code']);
		}
	}
	
	$okt['flashMessages']->success(__('Configuration captcha edited.'));
	
	http::redirect('module.php?m=accessible_captcha&action=index');
}

# Configuration envoyée
if (!empty($_POST['config_send']))
{
	$p_ = !empty($_POST['p_']) ? true : false;
	
	if ($okt->error->isEmpty())
	{
		$aNewConf = [];
		
		$okt->accessible_captcha->config->write($aNewConf);
		
		$okt['flashMessages']->success(__('Configuration captcha edited.'));
		
		http::redirect('module.php?m=accessible_captcha&action=index');
	}
}

/* Affichage
----------------------------------------------------------*/

# Récupération de la liste des questions
$rsQuestions = $okt->accessible_captcha->get();

# Titre de la page
$okt->page->addGlobalTitle('Accessible Captcha');

# En-tête
require OKT_ADMIN_HEADER_FILE;
?>

<!--
<form action="module.php" method="post">

	<p><?php echo form::hidden('m','accessible_captcha'); ?>
	<?php echo form::hidden(array('config_send'), 1); ?>
	<?php echo form::hidden(array('action'), 'index'); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" /></p>
</form>
-->

<?php # Gestion des questions ?>
<form action="module.php" method="post">

	<?php 
# Boucle sur les langues
	foreach ($okt['languages']->getList() as $aLanguage)
	:
		?>

	<fieldset>
		<legend><?php
		if ($okt['languages']->hasUniqueLanguage())
		{
			_e('Questions and answers');
		}
		else
		{
			echo html::escapeHTML($aLanguage['title']);
		}
		?></legend>

		<?php 
# Boucle sur les questions
		while ($rsQuestions->fetch())
		:
			if ($rsQuestions->language_code == $aLanguage['code'])
			:
				?>

			<p class="field"><?php echo form::text(array('questions['.$aLanguage['code'].']['.$rsQuestions->id.']'), 60, 255, html::escapeHTML($rsQuestions->question),'left')?>
			<?php echo form::text(array('reponses['.$aLanguage['code'].']['.$rsQuestions->id.']'), 40, 255, html::escapeHTML($rsQuestions->reponse),'left') ?></p>

		
			<?php endif;
		endwhile
		;
		?>

		<p class="field"><?php echo form::text('question_add['.$aLanguage['code'].']', 60, 255, '', 'left')?>
		<?php echo form::text('reponse_add['.$aLanguage['code'].']', 40, 255, '', 'left') ?></p>
	</fieldset>

	<?php endforeach; ?>

	<p><?php echo form::hidden('m','accessible_captcha'); ?>
	<?php echo form::hidden(array('manage_questions'), 1); ?>
	<?php echo form::hidden(array('action'), 'index'); ?>
	<?php echo Page::formtoken(); ?>
	<input type="submit" value="<?php _e('c_c_action_save') ?>" />
	</p>
</form>

<?php 
# Pied-de-page
require OKT_ADMIN_FOOTER_FILE;
?>
