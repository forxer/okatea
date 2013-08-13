<?php
/**
 * Création des premières pages
 *
 * @addtogroup Okatea
 * @subpackage 		Install interface
 *
 */

if (!defined('OKT_INSTAL_PROCESS')) die;


/* Initialisations
------------------------------------------------------------*/

# Inclusion du prepend
define('OKT_SKIP_CSRF_CONFIRM', true);
require_once dirname(__FILE__).'/../../../oktInc/admin/prepend.php';

# Locales
l10n::set(OKT_INSTAL_DIR.'/inc/locales/'.$_SESSION['okt_install_language'].'/install');
l10n::set(OKT_LOCALES_PATH.'/'.$_SESSION['okt_install_language'].'/admin.modules');

# est-ce qu'on as le module pages ?
$bHasPagesModule = $okt->modules->moduleExists('pages');


$aFirstPages = array(
	1 => array(
		'title' => ''
	),
	2 => array(
		'title' => ''
	),
	3 => array(
		'title' => ''
	),
	4 => array(
		'title' => ''
	),
	5 => array(
		'title' => ''
	),
	6 => array(
		'title' => ''
	),
	7 => array(
		'title' => ''
	)
);

$iPageHome = 0;


/* Traitements
------------------------------------------------------------*/

# json for adding new line
if (!empty($_REQUEST['getNewLine']))
{
	$i = !empty($_REQUEST['newId']) ? intval($_REQUEST['newId']) : null;

	if (!empty($i))
	{
		echo
		'<div class="two-cols page-line" id="page-line-'.$i.'">
			<p class="col field"><label for="p_first_pages_'.$i.'_title">'.sprintf(__('i_pages_page_title_%s'), $i).'</label>'.
			form::text(array('p_first_pages['.$i.'][title]','p_first_pages_'.$i.'_title'), 80, 255, '').'</p>'.

			'<p class="col field" style="padding-top: 1.5em;"><label for="p_page_home_'.$i.'">'.
			form::radio(array('p_page_home', 'p_page_home_'.$i), $i, ($iPageHome == $i)).
			sprintf(__('i_pages_page_home_%s'), $i).'</label></p>'.
		'</div>';
	}

	exit;
}

# formulaire envoyé
if (!empty($_POST['sended']))
{
	if ($bHasPagesModule)
	{
		$aFirstPages = !empty($_POST['p_first_pages']) && is_array($_POST['p_first_pages']) ? $_POST['p_first_pages'] : array();
		$iPageHome = !empty($_POST['p_page_home']) ? intval($_POST['p_page_home']) : 0;

		# création des premières pages
		$aAddedPages = array();
		foreach ($aFirstPages as $i=>$aPageInfos)
		{
			if (!empty($aPageInfos['title']))
			{
				$iNewId = $okt->pages->addPage(
					$okt->pages->openPageCursor(array(
						'active' => 1
					)),
					array(
						'fr' => array(
							'title' => $aPageInfos['title'],
							'content' => '<div id="enrichissement"><p>Site en cours d’enrichissement, merci de revenir le consulter ultérieurement.</p></div>'
						)
					),
					array()
				);

				if ($iPageHome == $i)
				{
					$iPageHome = $iNewId;
					$iPageHomeSlug = util::strToSlug($aPageInfos['title']);
				}
			}
		}

		# défintion d'une route par défaut
		if (!empty($iPageHome))
		{
			try
			{
				$okt->config->write(array(
					'default_route' => array(
						'class' => 'pagesController',
						'method' => 'pagesItem',
						'args' => $iPageHomeSlug
					)
				));
			}
			catch (InvalidArgumentException $e)
			{
				$okt->error->set(__('c_c_error_writing_configuration'));
				$okt->error->set($e->getMessage());
			}
		}
	}

	http::redirect('index.php?step='.$stepper->getNextStep());
}


/* Affichage
------------------------------------------------------------*/

$oHtmlPage->js->addReady('
	$("#add-line-button")
		.append("<a href=\"#\">'.__('i_pages_add_one_more').'</a>")
		.button()
		.click(function(event){
			event.preventDefault();
			var iNewId = $(".page-line").length + 1;

			$.get("index.php", {step: "pages", getNewLine: 1, newId: iNewId }, function(data){
				$("#page-lines").append(data);
			});
		});
');

# En-tête
$title = __('i_pages_title');
require OKT_INSTAL_DIR.'/header.php'; ?>


<form action="index.php" method="post">

<?php if (!$bHasPagesModule) : ?>
	<p><?php _e('i_pages_no_module_pages') ?>

<?php else : ?>

	<div id="page-lines">
		<?php foreach ($aFirstPages as $i=>$aPageInfos) : ?>
		<div class="two-cols page-line" id="page-line-<?php echo $i ?>">
			<p class="col field"><label for="p_first_pages_<?php echo $i ?>_title"><?php printf(__('i_pages_page_title_%s'), $i) ?></label>
			<?php echo form::text(array('p_first_pages['.$i.'][title]','p_first_pages_'.$i.'_title'), 80, 255, $aPageInfos['title']) ?></p>

			<p class="col field" style="padding-top: 1.5em;"><label for="p_page_home_<?php echo $i ?>"><?php echo form::radio(array('p_page_home', 'p_page_home_'.$i), $i, ($iPageHome == $i)) ?>
			<?php printf(__('i_pages_page_home_%s'), $i) ?></label></p>
		</div>
		<?php endforeach; ?>
	</div>

	<div class="two-cols">
		<p class="col" style="padding-top: 1em;"><span id="add-line-button"></span></p>

		<p class="col field" style="padding-top: 1.5em;"><label for="p_page_home_0"><?php echo form::radio(array('p_page_home','p_page_home_0'), 0, ($iPageHome == 0)) ?>
		<?php _e('i_pages_page_no_home') ?></label></p>
	</div>

<?php endif; ?>
	<p><input type="submit" value="<?php _e('c_c_next') ?>" />
	<input type="hidden" name="sended" value="1" />
	<input type="hidden" name="step" value="<?php echo $stepper->getCurrentStep() ?>" /></p>
</form>

<?php # Pied de page
require OKT_INSTAL_DIR.'/footer.php'; ?>
