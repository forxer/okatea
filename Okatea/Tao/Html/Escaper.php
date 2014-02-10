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
	 * @param string $value	String to escape
	 * @return	string
	 */
	public static function html($value)
	{
		return is_string($value) ? htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false) : $value;
	}

	/**
	 * Javascript escape
	 *
	 * Returns a protected JavaScript string
	 *
	 * @param string $value	String to escape
	 * @return	string
	 */
	public static function js($value)
	{
		$callback = function ($matches) {
			$char = $matches[0];

			if (!isset($char[1])) {
				return '\\x'.substr('00'.bin2hex($char), -2);
			}

			return '\\u'.substr('0000'.bin2hex($char), -4);
		};

		if (null === $value = preg_replace_callback('#[^\p{L}\p{N} ]#u', $callback, $value)) {
			throw new \InvalidArgumentException('The string to escape is not a valid UTF-8 string.');
		}

		return $value;
	}

	/**
	 * HTML attributes escape
	 *
	 * @param string $value	String to escape
	 * @return	string
	 */
	public static function attribute($value)
	{
		if (0 == strlen($value) ? false : (1 == preg_match('/^./su', $value) ? false : true)) {
			throw new \InvalidArgumentException('The string to escape is not a valid UTF-8 string.');
		}

		$value = preg_replace_callback('#[^a-zA-Z0-9,\.\-_]#Su', array('self','escapeHtmlAttrCallback'), $value);

		return $value;
	}

	/**
	 * This function is adapted from code coming from Zend Framework.
	 *
	 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
	 * @license   http://framework.zend.com/license/new-bsd New BSD License
	 */
	public static function escapeHtmlAttrCallback($matches)
	{
		/*
		 * While HTML supports far more named entities, the lowest common denominator
		 * has become HTML5's XML Serialisation which is restricted to the those named
		 * entities that XML supports. Using HTML entities would result in this error:
		 * XML Parsing Error: undefined entity
		 */
		static $entityMap = array(
			34 => 'quot', /* quotation mark */
			38 => 'amp',  /* ampersand */
			60 => 'lt',   /* less-than sign */
			62 => 'gt',   /* greater-than sign */
		);

		$chr = $matches[0];
		$ord = ord($chr);

		/**
		 * The following replaces characters undefined in HTML with the
		 * hex entity for the Unicode replacement character.
		*/
		if (($ord <= 0x1f && $chr != "\t" && $chr != "\n" && $chr != "\r") || ($ord >= 0x7f && $ord <= 0x9f)) {
			return '&#xFFFD;';
		}

		/**
		 * Check if the current character to escape has a name entity we should
		 * replace it with while grabbing the hex value of the character.
		 */
		if (strlen($chr) == 1) {
			$hex = strtoupper(substr('00'.bin2hex($chr), -2));
		} else {
			//$chr = $this->convertEncoding($chr, 'UTF-16BE', 'UTF-8');
			$hex = strtoupper(substr('0000'.bin2hex($chr), -4));
		}

		$int = hexdec($hex);
		if (array_key_exists($int, $entityMap)) {
			return sprintf('&%s;', $entityMap[$int]);
		}

		/**
		 * Per OWASP recommendations, we'll use hex entities for any other
		 * characters where a named entity does not exist.
		 */

		return sprintf('&#x%s;', $hex);
	}
}
