<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * La page de création de fichier de l'éditeur de themes.
 *
 * @addtogroup Okatea
 */

use Okatea\Admin\Page;
use Okatea\Tao\Misc\Utilities;
use Okatea\Tao\Forms\Statics\FormElements as form;
use Okatea\Tao\Themes\TemplatesSet;
use Symfony\Component\Filesystem\Filesystem;


# Accès direct interdit
if (!defined('ON_THEME_EDITOR')) die;


/* Initialisations
----------------------------------------------------------*/


class veryBadHackClass
{
	protected $aCurrent;

	public function getTemplateInfos($sDir)
	{
		$this->aCurrent = array();

		if (file_exists($sDir.'/_define.php')) {
			include $sDir.'/_define.php';
		}

		return $this->aCurrent;
	}
}


$p_tpl_id = '';
$p_tpl_name = '';
$p_tpl_desc = '';
$p_tpl_version = '';
$p_tpl_author = '';
$p_tpl_tags = '';


$sBasicTemplatePath = $okt->options->get('themes_dir').$sBasicTemplate;

$sBasicTemplateTheme = TemplatesSet::getThemeIdFromTplPath($sBasicTemplatePath);

$sBasicTemplateId = basename(dirname($sBasicTemplate));

$o = new veryBadHackClass();
$aTemplateInfo = $o->getTemplateInfos(dirname($sBasicTemplatePath));
unset($o);


$p_tpl_id = $sBasicTemplateId;

if (!empty($aTemplateInfo))
{
	$p_tpl_name = $aTemplateInfo['name'];
	$p_tpl_desc = $aTemplateInfo['desc'];
	$p_tpl_version = $aTemplateInfo['version'];
	$p_tpl_author = $aTemplateInfo['author'];
	$p_tpl_tags = $aTemplateInfo['tags'];
}


/* Traitements
----------------------------------------------------------*/

# formulaire envoyé
if (!empty($_POST['form_sent']) && $sThemeId)
{
	$p_tpl_id = !empty($_POST['p_tpl_id']) ? $_POST['p_tpl_id'] : '';
	$p_tpl_name = !empty($_POST['p_tpl_name']) ? $_POST['p_tpl_name'] : '';
	$p_tpl_desc = !empty($_POST['p_tpl_desc']) ? $_POST['p_tpl_desc'] : '';
	$p_tpl_version = !empty($_POST['p_tpl_version']) ? $_POST['p_tpl_version'] : '';
	$p_tpl_author = !empty($_POST['p_tpl_author']) ? $_POST['p_tpl_author'] : '';
	$p_tpl_tags = !empty($_POST['p_tpl_tags']) ? $_POST['p_tpl_tags'] : '';

	$p_tpl_id = Utilities::strToLowerURL($p_tpl_id,false);

	$sTemplate = str_replace(
		array(
			$sBasicTemplateTheme.'/templates',
			$sBasicTemplateId.'/template.php'
		),
		array(
			$sThemeId.'/templates',
			$p_tpl_id.'/template.php'
		),
		$sBasicTemplate);

	$sTemplatePath = $okt->options->get('themes_dir').$sTemplate;

	if (empty($p_tpl_id)) {
		$okt->error->set(__('c_a_te_error_must_tpl_id'));
	}
	elseif ($p_tpl_id == $sBasicTemplateId) {
		$okt->error->set(__('c_a_te_error_same_tpl_id'));
	}
	elseif (file_exists($sTemplatePath)) {
		$okt->error->set(sprintf(__('c_a_te_error_tpl_%s_allready_exists'), $sTemplate));
	}

	if ($okt->error->isEmpty())
	{
		$fs = new Filesystem();
		$fs->mirror(dirname($sBasicTemplatePath), dirname($sTemplatePath));

		file_put_contents(dirname($sTemplatePath).'/_define.php',
			'<?php'."\n\n".
			'$this->aCurrent = array('."\n".
			'	\'name\' 		=> \''.addslashes($p_tpl_name).'\','."\n".
			'	\'desc\' 		=> \''.addslashes($p_tpl_desc).'\','."\n".
			'	\'version\' 	=> \''.addslashes($p_tpl_version).'\','."\n".
			'	\'author\' 		=> \''.addslashes($p_tpl_author).'\','."\n".
			'	\'tags\' 		=> \''.addslashes($p_tpl_tags).'\''."\n".
			');'."\n"
		);

		http::redirect('configuration.php?action=theme_editor&amp;theme='.$sThemeId.'&amp;file='.rawurlencode(str_replace('/'.$sThemeId.'/templates', '/templates', $sTemplate)));
	}
}


/* Affichage
----------------------------------------------------------*/

# Infos page
$okt->page->addGlobalTitle(__('c_a_theme_editor'), 'configuration.php?action=theme_editor');

if ($sThemeId) {
	$okt->page->addGlobalTitle($oThemeEditor->getThemeInfo('name'), 'configuration.php?action=theme_editor&theme='.$sThemeId);
}

$okt->page->addGlobalTitle(__('c_a_te_new_tpl'), 'configuration.php?action=theme_editor&theme='.$sThemeId.'&new_file=1');

$okt->page->strToSlug('#p_tpl_name','#p_tpl_id');

# En-tête
require OKT_ADMIN_HEADER_FILE; ?>


<form action="configuration.php" method="post">

	<div class="two-cols">
		<p class="field col"><label for="p_tpl_name"><?php _e('c_a_te_tpl_name') ?></label>
		<?php echo form::text('p_tpl_name', 40, 256, $p_tpl_name) ?></p>

		<p class="field col"><label for="p_tpl_id"><?php _e('c_a_te_tpl_id') ?></label>
		<?php echo form::text('p_tpl_id', 40, 256, $p_tpl_id) ?></p>

		<p class="field col"><label for="p_tpl_desc"><?php _e('c_a_te_tpl_desc') ?></label>
		<?php echo form::text('p_tpl_desc', 60, 256, $p_tpl_desc) ?></p>

		<p class="field col"><label for="p_tpl_version"><?php _e('c_a_te_tpl_version') ?></label>
		<?php echo form::text('p_tpl_version', 40, 256, $p_tpl_version) ?></p>

		<p class="field col"><label for="p_tpl_author"><?php _e('c_a_te_tpl_author') ?></label>
		<?php echo form::text('p_tpl_author', 40, 256, $p_tpl_author) ?></p>

		<p class="field col"><label for="p_tpl_tags"><?php _e('c_a_te_tpl_tags') ?></label>
		<?php echo form::text('p_tpl_tags', 40, 256, $p_tpl_tags) ?></p>
	</div>

	<p><?php echo form::hidden(array('action'), 'theme_editor') ?>
	<?php echo form::hidden('theme', $sThemeId) ?>
	<?php echo form::hidden('basic_template', rawurlencode($sBasicTemplate)) ?>
	<?php echo form::hidden('new_template', 1) ?>
	<?php echo form::hidden('form_sent', 1) ?>
	<?php echo Page::formtoken() ?>
	<input type="submit" name="save" value="<?php _e('c_c_action_Save') ?>" /></p>
</form>



<?php # Pied-de-page
require OKT_ADMIN_FOOTER_FILE; ?>
