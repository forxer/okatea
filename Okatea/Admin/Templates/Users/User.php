<?php

use Okatea\Tao\Forms\Statics\FormElements as form;

$view->extend('layout');

# titre de la page
$okt->page->addGlobalTitle(__('c_a_menu_users'), $view->generateUrl('Users_index'));

?>

