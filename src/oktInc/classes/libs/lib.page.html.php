<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * @class pageHtml
 * @ingroup okt_classes_libs
 * @brief Génération d'éléments HTML
 *
 */
class pageHtml
{
	/**
	 * Retourne une liste non-ordonnée à partir d'un tableau.
	 *
	 * @param array $data
	 * @param string $class
	 * @return string
	 */
	public static function ul($data,$class=null)
	{
		return '<ul'.($class ? ' class="'.$class.'"' : '').'><li>'.implode('</li><li>'.$data).'</li></ul>';
	}

	/**
	 * Retourne une liste ordonnée à partir d'un tableau.
	 *
	 * @param array $data
	 * @param string $class
	 * @return string
	 */
	public static function ol($data,$class=null)
	{
		return '<ol'.($class ? ' class="'.$class.'"' : '').'><li>'.implode('</li><li>'.$data).'</li></ol>';
	}

	/**
	 * Image
	 *	- file = file (and path) of image (required)
	 *	- height = image height (optional, default actual height)
	 * 	- width = image width (optional, default actual width)
	 *	- basedir = base directory for absolute paths, default
	 *              is environment variable DOCUMENT_ROOT
	 *	- path_prefix = prefix for path output (optional, default empty)
	 *
	 * @param $params
	 * @return unknown_type
	 */
	public static function img($params=array())
	{
		$alt = '';
		$file = '';
		$height = '';
		$width = '';
		$extra = '';
		$prefix = '';
		$suffix = '';
		$path_prefix = '';
		$basedir = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '';

		foreach($params as $_key => $_val)
		{
			switch($_key)
			{
				case 'file':
				case 'height':
				case 'width':
				case 'path_prefix':
				case 'basedir':
					$$_key = $_val;
					break;

				case 'alt':
					$$_key = html::escapeHTML($_val);
					break;

				case 'link':
				case 'href':
					$prefix = '<a href="' . $_val . '">';
					$suffix = '</a>';
					break;

				default:
					$extra .= ' '.$_key.'="'.html::escapeHTML($_val).'"';
					break;
			}
		}

		if (empty($file)) {
			trigger_error("pageHtml::img : missing 'file' parameter", E_USER_NOTICE);
			return;
		}

		if (substr($file,0,1) == '/') {
			$_image_path = $basedir . $file;
		} else {
			$_image_path = $file;
		}

		if (!isset($params['width']) || !isset($params['height']))
		{
			if (!$_image_data = getimagesize($_image_path))
			{
				if (!file_exists($_image_path)) {
					trigger_error("pageHtml::img : unable to find '$_image_path'", E_USER_NOTICE);
					return;
				}
				else if (!is_readable($_image_path)) {
					trigger_error("pageHtml::img : unable to read '$_image_path'", E_USER_NOTICE);
					return;
				}
				else {
					trigger_error("pageHtml::img : '$_image_path' is not a valid image file", E_USER_NOTICE);
					return;
				}
			}

			if (!isset($params['width'])) {
				$width = $_image_data[0];
			}

			if (!isset($params['height'])) {
				$height = $_image_data[1];
			}

		}

		return $prefix.'<img src="'.$path_prefix.$file.'" alt="'.$alt.'" width="'.$width.'" height="'.$height.'"'.$extra.' />'.$suffix;
	}

} # class
