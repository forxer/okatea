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


/**
 * @class filemanager_old
 * @ingroup okt_classes_tools
 * @brief
 *
 * @TODO: for convenience we must used clearbricks filemanager
 */
class filemanager_old
{
	var $root;
	var $exclude_list = array();
	var $_types = array('img'=>'','txt'=>'');


	function filemanager($root_path,$base_path)
	{
		$this->root = path::real($root_path);
		$this->base_path = $this->__cleanPath('/'.$base_path);

		# Type de fichiers images
		$this->_types['img'] = '.gif|.jpg|.jpeg|.png|.bmp';

		# Types de fichier texte (éditables)
		$this->_types['txt'] = '.htm|.html|.php|.php3|.inc|.xml|.txt|.xhtml|.css';
	}

	function addExclusion($f)
	{
		if (is_array($f))
		{
			$l = array();
			foreach ($f as $v)
			{
				if (($V = path::real($v)) !== false)
					$this->exclude_list[] = $V;
			}
		}
		elseif (($F = path::real($f)) !== false)
		{
			$this->exclude_list[] = $F;
		}
	}

	function isExclude()
	{
		foreach ($this->exclude_list as $v)
		{
			if (strpos($this->root.$this->base_path,$v) === 0)
				return true;
		}

		return false;
	}

	function getDir()
	{
		$dir = $this->__cleanPath($this->root.$this->base_path);

		if ($dh = @opendir($dir))
		{
			$d_res = $f_res = array();

			while (($file = readdir($dh)) !== false)
			{
				$fname = $dir.'/'.$file;
				$jailed = $this->__inJail($fname);
				$tmp = array();

				$stat = stat($fname);

				$tmp['fname'] = $fname;
				$tmp['jail'] = $jailed;
				$tmp['type'] = null;
				$tmp['type'] = filetype($fname);
				$tmp['mtime'] = $stat[9];
				$tmp['size'] = $stat[7];
				$tmp['mode'] = $stat[2];
				$tmp['uid'] = $stat[4];
				$tmp['gid'] = $stat[5];
				$tmp['w'] = $tmp['r'] = $tmp['x'] = $tmp['f'] = $tmp['del'] = false;
				$tmp['d'] = ($file == '..');
				$tmp['l'] = null;

				if ($jailed && !in_array($fname,$this->exclude_list))
				{
					$tmp['w'] = is_writable($fname);
					$tmp['r'] = is_readable($fname);
					$tmp['x'] = file_exists($fname.'/.');
					$tmp['f'] = is_file($fname);
					$tmp['d'] = is_dir($fname);
					$tmp['l'] = $this->__getRelPath($fname);
					$tmp['del'] = ($file != '.') ? files::isDeletable($fname) : false;
					$tmp['type'] = $this->__getType($fname);
				}

				if (is_dir($fname))
					$d_res[$file] = $tmp;
				else
					$f_res[$file] = $tmp;
			}
			closedir($dh);

			ksort($d_res);
			ksort($f_res);

			return array('dirs'=>$d_res,'files'=>$f_res);
		}

		return false;
	}

	function getContent()
	{
		if ($this->isFile() && $this->isReadable()) {
			return file_get_contents($this->root.$this->base_path);
		}

		return null;
	}

	function isFile()
	{
		return is_file($this->root.$this->base_path);
	}

	function isDir()
	{
		return is_dir($this->root.$this->base_path);
	}

	function isWritable()
	{
		return is_writable($this->root.$this->base_path);
	}

	function isReadable()
	{
		return is_readable($this->root.$this->base_path);
	}

	function isDeletable()
	{
		return files::isDeletable($this->root.$this->base_path);
	}

	function isParentWritable()
	{
		return is_writable(dirname($this->root.$this->base_path));
	}

	function isImg()
	{
		return ($this->__getType($this->root.$this->base_path) == 'img');
	}

	function isTxt()
	{
		return ($this->__getType($this->root.$this->base_path) == 'txt');
	}

	function newDir($name)
	{
		$name = str_replace('/','',$name);

		return files::makeDir($this->root.$this->base_path.'/'.$name);
	}

	function putContent($c)
	{
		return files::putContent($this->root.$this->base_path,$c);
	}

	function rename($name)
	{
		$name = str_replace('/','',$name);
		$d = $this->root.$this->base_path;
		$n = dirname($d).'/'.$name;

		return @rename($d,$n);
	}

	function delete()
	{
		$f = $this->root.$this->base_path;
		if (is_file($f))
		{
			if (@unlink($f) === true)
				return true;
		}
		elseif (is_dir($f))
		{
			if (@rmdir($f) === true)
				return true;
		}

		return false;
	}

	/* Methodes privées
	------------------------------------------------------- */
	function __cleanPath($p)
	{
		$p = str_replace('..','',$p);
		$p = preg_replace('|/{2,}|','/',$p);
		$p = preg_replace('|/$|','',$p);

		return $p;
	}

	function __inJail($f)
	{
		if (($f = path::real($f)) !== false)
		{
			if (preg_match('|^'.preg_quote($this->root,'|').'|',$f))
				return true;
			else
				return false;
		}

		return false;
	}

	function __getRelPath($f)
	{
		if ($this->__inJail($f))
		{
			$f = path::real($f);
			return preg_replace('|^'.preg_quote($this->root,'|').'|','',$f);
		}

		return false;
	}

	function __getType($f)
	{
		global $fm_cf_types;

		if (is_file($f))
		{
			if (strpos(basename($f),'.') === false)
				return 'txt';
			elseif (preg_match('/('.$this->_types['img'].')$/i',basename($f)))
				return 'img';
			elseif (preg_match('/('.$this->_types['txt'].')$/i',basename($f)))
				return 'txt';
		}
		else
			return null;
	}
}
