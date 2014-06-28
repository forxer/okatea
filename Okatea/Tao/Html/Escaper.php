<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Html;

class Escaper
{
	/**
	 * HTML escape
	 *
	 * Replaces HTML special characters by entities.
	 *
	 * @param string $value
	 *        	to escape
	 * @return string
	 */
	public static function html($value)
	{
		return is_string($value) ? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE) : $value;
		return is_string($value) ? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) : $value;
	}

	/**
	 * HTML attributes escape
	 *
	 * @param string $value
	 *        	to escape
	 * @return string
	 */
	public static function attribute($value)
	{
		return is_string($value) ? htmlspecialchars($value, ENT_COMPAT | ENT_SUBSTITUTE) : $value;
		return is_string($value) ? htmlspecialchars($value, ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8', false) : $value;
	}

	/**
	 * Javascript escape
	 *
	 * Returns a protected JavaScript string
	 *
	 * @param string $value
	 *        	to escape
	 * @return string
	 */
	public static function js($value)
	{
		if (is_string($value))
		{
			return $value;
		}

		$value = htmlspecialchars($value, ENT_NOQUOTES | ENT_SUBSTITUTE);
		$value = str_replace("'", "\'", $value);
		$value = str_replace('"', '\"', $value);

		return $value;
	}
}
