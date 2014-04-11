<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao;

use DateTime;
use IntlDateFormatter;

class Dates
{
	protected static $sLocale = 'en';

	protected static $sTimezone = 'UTC';

	public static function setLocale($sLocale)
	{
		self::$sLocale = $sLocale;
	}

	public static function setTimezone($sTimezone)
	{
		self::$sTimezone = $sTimezone;
	}

	public static function full(DateTime $date, $bWithTime = false)
	{
		return (new IntlDateFormatter(self::$sLocale, IntlDateFormatter::FULL, self::getTimeType($bWithTime)))
			->format($date);
	}

	public static function long(DateTime $date, $bWithTime = false)
	{
		return (new IntlDateFormatter(self::$sLocale, IntlDateFormatter::LONG, self::getTimeType($bWithTime)))
			->format($date);
	}

	public static function medium(DateTime $date, $bWithTime = false)
	{
		return (new IntlDateFormatter(self::$sLocale, IntlDateFormatter::MEDIUM, self::getTimeType($bWithTime)))
			->format($date);
	}

	public static function short(DateTime $date, $bWithTime = false)
	{
		return (new IntlDateFormatter(self::$sLocale, IntlDateFormatter::SHORT, self::getTimeType($bWithTime)))
			->format($date);
	}

	protected static function getTimeType($bWithTime = false)
	{
		if ($bWithTime) {
			return IntlDateFormatter::SHORT;
		}

		return IntlDateFormatter::NONE;
	}
}
