<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\Html;

class Modifiers
{
	/**
	 * Convert \r\n an \r in \n
	 *
	 * @param string $str      String to transform
	 * @return string
	 */
	public static function linebreaks($str)
	{
		return str_replace(
			array("\r\n", "\r"),
			array("\n", "\n"),
			$str
		);
	}

	/**
	 * Converts text line breaks into HTML paragraphs.
	 *
	 * @param string $str      String to transform
	 * @return string
	 */
	public static function nlToP($str)
	{
		$str = trim($str);
		$str = self::linebreaks($str);
		$str = str_replace("\n", "</p>\n<p>", $str);
		$str = str_replace('<p></p>', '', $str);
		return '<p>'.$str.'</p>'.PHP_EOL;
	}

	/**
	 * Converts text line breaks into HTML paragraphs and HTML line breaks.
	 *
	 * @param string $str      String to transform
	 * @return string
	 */
	public static function nlToPbr($str)
	{
		$str = trim($str);
		$str = self::linebreaks($str);
		$str = str_replace("\n", '<br />', $str);
		$str = str_replace('<br /><br />', "</p>\n<p>", $str);
		$str = str_replace('<p></p>', '', $str);
		return '<p>'.$str.'</p>'.PHP_EOL;
	}

	/**
	 * Transform a string in slug regarding to configuration.
	 *
	 * @param string	$str			String to transform
	 * @param boolean	$bWithSlashes	Keep slashes in URL
	 * @return string
	 */
	static public function strToSlug($str, $bWithSlashes = true)
	{
		switch ($GLOBALS['okt']->config->slug_type)
		{
			case 'utf8':
				return self::tidyURL($str, $bWithSlashes);

			case 'ascii':
			default:
				return self::strToLowerURL($str, $bWithSlashes);
		}
	}

	/**
	 * String to URL
	 *
	 * Transforms a string to a proper URL.
	 *
	 * @copyright Copyright (c) 2003-2013 Olivier Meunier & Association Dotclear
	 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
	 *
	 * @param string	$str			String to transform
	 * @param boolean	$bWithSlashes	Keep slashes in URL
	 * @return string
	 */
	public static function strToUrl($str, $bWithSlashes = true)
	{
		$str = self::deaccent($str);
		$str = preg_replace('/[^A-Za-z0-9_\s\'\:\/[\]-]/', '', $str);

		return self::tidyUrl($str, $bWithSlashes);
	}

	/**
	 * String to lower URL.
	 *
	 * Transforms a string to a lowercase proper URL.
	 *
	 * @param string	$str			String to transform
	 * @param boolean	$bWithSlashes	Keep slashes in URL
	 * @return string
	 */
	public static function strToLowerUrl($str, $bWithSlashes = true)
	{
		return strtolower(self::strToUrl($str, $bWithSlashes));
	}

	/**
	 * Transform a string in a camelCase style.
	 *
	 * @param string $str
	 * @return string
	 */
	static public function strToCamelCase($str)
	{
		$str = self::strToLowerUrl($str, false);

		$str = implode('', array_map('ucfirst', explode('_',$str)));
		$str = implode('', array_map('ucfirst', explode('-',$str)));

		return strtolower(substr($str, 0, 1)).substr($str, 1);
	}

	/**
	 * Transform a string in underscored style.
	 *
	 * @param string $str
	 * @return string
	 */
	static public function strToUnderscored($str)
	{
		$str = self::strToLowerUrl($str, false);

		return str_replace('-', '_', $str);
	}

	/**
	 * Accents replacement
	 *
	 * Replaces some occidental accentuated characters by their ASCII
	 * representation.
	 *
	 * @copyright Copyright (c) 2003-2013 Olivier Meunier & Association Dotclear
	 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
	 *
	 * @param	string	$str		String to deaccent
	 * @return	string
	 */
	public static function deaccent($str)
	{
		$pattern['A'] = '\x{00C0}-\x{00C5}';
		$pattern['AE'] = '\x{00C6}';
		$pattern['C'] = '\x{00C7}';
		$pattern['D'] = '\x{00D0}';
		$pattern['E'] = '\x{00C8}-\x{00CB}';
		$pattern['I'] = '\x{00CC}-\x{00CF}';
		$pattern['N'] = '\x{00D1}';
		$pattern['O'] = '\x{00D2}-\x{00D6}\x{00D8}';
		$pattern['OE'] = '\x{0152}';
		$pattern['S'] = '\x{0160}';
		$pattern['U'] = '\x{00D9}-\x{00DC}';
		$pattern['Y'] = '\x{00DD}';
		$pattern['Z'] = '\x{017D}';

		$pattern['a'] = '\x{00E0}-\x{00E5}';
		$pattern['ae'] = '\x{00E6}';
		$pattern['c'] = '\x{00E7}';
		$pattern['d'] = '\x{00F0}';
		$pattern['e'] = '\x{00E8}-\x{00EB}';
		$pattern['i'] = '\x{00EC}-\x{00EF}';
		$pattern['n'] = '\x{00F1}';
		$pattern['o'] = '\x{00F2}-\x{00F6}\x{00F8}';
		$pattern['oe'] = '\x{0153}';
		$pattern['s'] = '\x{0161}';
		$pattern['u'] = '\x{00F9}-\x{00FC}';
		$pattern['y'] = '\x{00FD}\x{00FF}';
		$pattern['z'] = '\x{017E}';

		$pattern['ss'] = '\x{00DF}';

		foreach ($pattern as $r => $p) {
			$str = preg_replace('/['.$p.']/u', $r, $str);
		}

		return $str;
	}

	/**
	 * URL cleanup
	 *
	 * @copyright Copyright (c) 2003-2013 Olivier Meunier & Association Dotclear
	 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
	 *
	 * @param string	$str			URL to tidy
	 * @param boolean	$bKeepSlashes	Keep slashes in URL
	 * @param boolean	$bKeepSpaces	Keep spaces in URL
	 * @return string
	 */
	public static function tidyUrl($str, $bKeepSlashes = true, $bKeepSpaces = false)
	{
		$str = strip_tags($str);
		$str = str_replace(array('?', '&', '#', '=', '+', '<', '>', '"', '%'), '', $str);
		$str = str_replace("'", ' ', $str);
		$str = preg_replace('/[\s]+/u', ' ', trim($str));

		if (!$bKeepSlashes) {
			$str = str_replace('/', '-', $str);
		}

		if (!$bKeepSpaces) {
			$str = str_replace(' ', '-', $str);
		}

		$str = preg_replace('/[-]+/', '-', $str);

		# Remove path changes in URL
		$str = preg_replace('%^/%', '', $str);
		$str = preg_replace('%\.+/%', '', $str);

		return $str;
	}
}
