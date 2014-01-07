<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Orignal file from Dotclear 2.
 * Copyright (c) 2003-2013 Olivier Meunier & Association Dotclear
 * Licensed under the GPL version 2.0 license.
 */

namespace Tao\FileManager;

use Tao\FileManager\Filemanager;

use Tao\Misc\Utilities;

/**
 * Un gestionnaire de fichiers en ligne.
 *
 * @deprecated
 */
class WebFileManager extends Filemanager
{
	public $p_url;
	public $p_img;

	private $get;

	public function __construct($root_path,$base_path,$p_url,$p_img,$get='f')
	{
		$this->get = $get;

		#
		# Chroot
		#
		# Ce paramètre est trés important, il définit le répertoire "racine"
		# du gestionnaire de fichier et vous garantit que l'utilisateur ne
		# remontera pas trop haut.
		#
		if (!isset($root_path)) {
			$root_path = (defined('PUBLIC_DIR') ? PUBLIC_DIR : '/upload/');
		}

		parent::filemanager($root_path,$base_path);

		#
		# Exclusion
		#
		# Ce paramètre définie la liste des fichiers et/ou répertoires qui ne seront
		# ni vu, ni modifiables, ni supprimables.
		#
		$default_fm_cf_exclusion = array (
			# Ne pas retirer ces entrées
			__DIR__
			,(defined('OKT_INC_PATH') ? OKT_INC_PATH : '/oktInc/')
			,(defined('OKT_CONFIG_PATH') ? OKT_INC_PATH : '/oktConf/')

			# Par exemple, pour exclure votre /index.html
			#, $_SERVER['DOCUMENT_ROOT'].'/index.html'
		);

		$this->addExclusion($default_fm_cf_exclusion);

		$this->p_url = $p_url;
		$this->p_img = $p_img;
	}

	public function getLink($params)
	{
		if (strpos($this->p_url, '?') === false) {
			return $this->p_url.'?'.$params;
		}

		return $this->p_url.'&amp;'.$params;
	}

	public function getNavBar()
	{
		$f = '';
		$res = '&#187; <a href="'.$this->p_url.'">racine</a>';

		if (file_exists($this->root.$this->base_path))
		{
			$r = explode('/',$this->base_path);
			for ($i=1; $i<count($r); $i++)
			{
				$f .= '/'.$r[$i];
				$res .= '/<a href="'.$this->getLink($this->get.'='.$f).'">'.$r[$i].'</a>';
			}
		}
		return $res;
	}

	public function getLine($k,$v,$c)
	{
		if ($k == '.') {
			return;
		}

		$td_class = $c == 1 ? 'odd' : 'even';

		$res = '<tr>';

		$res .= $this->getIconCell($v,$td_class);

		$res .= '<td class="'.$td_class.'">';
		if ($v['jail'] && $v['r'] && ($v['f'] || $v['d'] && $v['x'])) {
			$res .= '<a href="'.$this->getLink($this->get.'='.$v['l'].'&amp;dl=1').'">'.$k.'</a>';
		} else {
			$res .= $k;
		}

		$res .= '</td>';

		$filesize = filesize($v['fname']);

		$res .= '<td class="'.$td_class.' small nowrap">'.($filesize > 0 ? Utilities::l10nFileSize($filesize) : '-').'</td>';

		$res .= '<td class="'.$td_class.' small nowrap">'.strftime('%d-%m-%Y %H:%M:%S',$v['mtime']).'</td>';

		$res .= '<td class="'.$td_class.' small">';
		if ($v['del'])
		{
			$del_msg = sprintf('Etes-vous sûr de vouloir supprimer ce %s?',
			($v['d'] ? 'répertoire' : 'fichier'));

			$res .= '<a href="'.$this->getLink($this->get.'='.$v['l'].'&amp;del=1').'" '.
			'onclick="return window.confirm(\''.$del_msg.'\')">'.
			'<img src="'.$this->p_img.'delete.png" alt="supprimer" /></a> ';
		}

		if ($v['jail'] && $v['r'] && $v['f'])
		{
			$res .= '<a href="'.$this->getLink($this->get.'='.$v['l'].'&amp;dl=1').'">'.
			'<img src="'.$this->p_img.'download.png" alt="télécharger" /></a> ';
		}

		$res .= '</td>';

		$res .= '</tr>';

		return $res;
	}

	public function getPublicLine($k,$v,$c)
	{
		if ($k == '.') {
			return;
		}

		$td_class = $c == 1 ? 'odd' : 'even';

		$res = '<tr>';

		$res .= $this->getIconCell($v,$td_class);

		$res .= '<td class="'.$td_class.'">';
		if ($v['jail'] && $v['r'] && ($v['f'] || $v['d'] && $v['x'])) {
			$res .= '<a href="'.$this->getLink($this->get.'='.$v['l'].'&amp;dl=1').'">'.$k.'</a>';
		} else {
			$res .= $k;
		}

		$res .= '</td>';

		$filesize = filesize($v['fname']);

		$res .= '<td class="'.$td_class.' small nowrap">'.($filesize > 0 ? Utilities::l10nFileSize($filesize) : '-').'</td>';

		$res .= '<td class="'.$td_class.' small nowrap">'.strftime('%d-%m-%Y %H:%M:%S',$v['mtime']).'</td>';

		$res .= '<td class="'.$td_class.' small">';

		if ($v['jail'] && $v['r'] && $v['f'])
		{
			$res .= '<a href="'.$this->getLink($this->get.'='.$v['l'].'&amp;dl=1').'">'.
			'<img src="'.$this->p_img.'download.png" alt="télécharger" /></a> ';
		}

		$res .= '</td>';

		$res .= '</tr>';

		return $res;
	}

	private function getIconCell($v,$td_class)
	{
		$res = '<td class="'.$td_class.' small">';
		if ($v['d'] && $v['w']) {
			$res .= '<img src="'.$this->p_img.'folder.png" alt="répertoire" /> ';
		}
		elseif ($v['d']) {
			$res .= '<img src="'.$this->p_img.'folder-ro.png" alt="répertoire" /> ';
		}
		elseif ($v['f'] && $v['w'])
		{
			if ($v['type'] == 'img') {
				$res .= '<img src="'.$this->p_img.'image.png" alt="fichier" /> ';
			} else {
				$res .= '<img src="'.$this->p_img.'file.png" alt="fichier" /> ';
			}
		}
		else {
			if ($v['type'] == 'img') {
				$res .= '<img src="'.$this->p_img.'image-ro.png" alt="fichier" /> ';
			} else {
				$res .= '<img src="'.$this->p_img.'file-ro.png" alt="fichier" /> ';
			}
		}
		$res .= '</td>';

		return $res;
	}
}
