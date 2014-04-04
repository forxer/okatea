<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$view->extend('layout');

# Titre de la page
$okt->page->addTitleTag($okt->module('Contact')->getTitle());
$okt->page->addAriane($okt->module('Contact')->getName(), $view->generateUrl('Contact_index'));

$okt->page->addGlobalTitle(__('m_contact_fields'), $view->generateUrl('Contact_fields'));
$okt->page->addGlobalTitle(__('m_contact_fields_field_values'));
