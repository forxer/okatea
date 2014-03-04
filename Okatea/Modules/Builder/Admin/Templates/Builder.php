<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

$okt->page->css->addFile($okt->options->public_url.'/modules/Builder/builder.css');

# module title tag
$okt->page->addGlobalTitle(__('m_builder_menu'));

?>

<?php echo $stepper->display() ?>
