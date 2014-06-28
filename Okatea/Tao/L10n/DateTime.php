<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\L10n;

class DateTime
{
	public static function full($mDate = null)
	{
		return Date::full($mDate, true);
	}

	public static function long($mDate = null)
	{
		return Date::long($mDate, true);
	}

	public static function medium($mDate = null)
	{
		return Date::medium($mDate, true);
	}

	public static function short($mDate = null)
	{
		return Date::short($mDate, true);
	}
}
