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
require_once __DIR__.'/../../../oktInc/admin/prepend.php';

# Locales
l10n::set(OKT_INSTAL_DIR.'/inc/locales/'.$_SESSION['okt_install_language'].'/install');
l10n::set(OKT_LOCALES_PATH.'/'.$_SESSION['okt_install_language'].'/admin.modules');

# est-ce qu'on as le module pages ?
$bHasPagesModule = $okt->modules->moduleExists('pages');


$aFirstPages = array(
	1 => array(
		'title' => __('i_pages_first_home_title'),
		'content' => __('i_pages_first_home_content')
	),
	2 => array(
		'title' => __('i_pages_first_about_title'),
		'content' => ''
	),
	3 => array(
		'title' => '',
		'content' => ''
	),
	4 => array(
		'title' => '',
		'content' => ''
	)
);

$iPageHome = 1;


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
			<div class="col">'.
				'<p class="field"><label for="p_first_pages_'.$i.'_title">'.sprintf(__('i_pages_page_title_%s'), $i).'</label>'.
				form::text(array('p_first_pages['.$i.'][title]','p_first_pages_'.$i.'_title'), 80, 255, '').'</p>'.

				'<p class="field"><label for="p_first_pages_'.$i.'_content">'.sprintf(__('i_pages_page_content_%s'), $i).'</label>'.
				form::textarea(array('p_first_pages['.$i.'][content]','p_first_pages_'.$i.'_content'), 78, 4, '').'</p>'.
			'</div>'.

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
							'content' => !empty($aPageInfos['content']) ? util::nlToP($aPageInfos['content']) : __('i_pages_first_default_content')
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

	http::redirect('index.php?step='.$okt->stepper->getNextStep());
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
			<div class="col">
				<p class="field"><label for="p_first_pages_<?php echo $i ?>_title"><?php printf(__('i_pages_page_title_%s'), $i) ?></label>
				<?php echo form::text(array('p_first_pages['.$i.'][title]','p_first_pages_'.$i.'_title'), 80, 255, $aPageInfos['title']) ?></p>

				<p class="field"><label for="p_first_pages_<?php echo $i ?>_content"><?php printf(__('i_pages_page_content_%s'), $i) ?></label>
				<?php echo form::textarea(array('p_first_pages['.$i.'][content]','p_first_pages_'.$i.'_content'), 78, 4, $aPageInfos['content']) ?></p>
			</div>

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
	<input type="hidden" name="step" value="<?php echo $okt->stepper->getCurrentStep() ?>" /></p>
</form>

<?php # Pied de page
require OKT_INSTAL_DIR.'/footer.php'; ?>
