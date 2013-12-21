<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Outil infos Okatea (partie affichage)
 *
 * @addtogroup Okatea
 *
 */

use Tao\Misc\Utilities as util;
use Tao\Html\CheckList;

# Accès direct interdit
if (!defined('ON_OKT_CONFIGURATION')) die;


$oktVersion = util::getVersion();
$oktRevision = util::getRevision();


# vérification des pré-requis
$okt->l10n->loadFile(OKT_LOCALES_PATH.'/'.$okt->user->language.'/pre-requisites');
require OKT_INC_PATH.'/systeme_requirements.php';

foreach ($requirements as $group)
{
	${'check_'.$group['group_id']} = new CheckList();

	foreach ($group['requirements'] as $requirement) {
		${'check_'.$group['group_id']}->addItem($requirement['id'],$requirement['test'],$requirement['msg_ok'],$requirement['msg_ko']);
	}
}

$pass_test = true;
$warning_empty = true;

foreach ($requirements as $group)
{
	$pass_test = $pass_test && ${'check_'.$group['group_id']}->checkAll();
	$warning_empty = $warning_empty && !${'check_'.$group['group_id']}->checkWarnings();
}

?>

<h3><?php _e('c_a_infos_okatea_title') ?></h3>

<p>Okatea
	<?php if ($oktVersion) { echo ' '.__('c_a_infos_okatea_version').' <strong>'.$oktVersion.'</strong> '; } ?>
	<?php if ($oktRevision) { echo ' '.__('c_a_infos_okatea_revision').' <em>'.$oktRevision.'</em> '; } ?>
	[<a href="configuration.php?action=infos&amp;show_changelog=1" id="changelog_link">changelog</a>]
</p>

<h4><?php _e('c_a_infos_okatea_prerequisites') ?></h4>
<?php foreach ($requirements as $group) : ?>

	<h5><?php echo $group['group_title'] ?></h5>
	<?php echo ${'check_'.$group['group_id']}->getHTML(); ?>

<?php endforeach; ?>

<?php if (!$pass_test) : ?>
<p><?php _e('c_a_infos_okatea_big_loose') ?></p>
<?php else : ?>
	<?php if (!$warning_empty) : ?>
	<p><?php _e('c_a_infos_okatea_warning') ?></p>
	<?php endif; ?>
<?php endif; ?>
