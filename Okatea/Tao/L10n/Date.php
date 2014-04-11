<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Okatea\Tao\L10n;

use Carbon\Carbon;
use DateTime;
use IntlDateFormatter;
use Okatea\Tao\Misc\Utilities;

class Date
{
	protected static $sLocale = 'en';

	protected static $sTimezone = 'UTC';

	/**
	 * Define locale to use.
	 * @param string $sLocale
	 */
	public static function setLocale($sLocale)
	{
		self::$sLocale = $sLocale;
	}

	/**
	 * Define timezone to use.
	 * @param string $sTimezone
	 */
	public static function setTimezone($sTimezone)
	{
		self::$sTimezone = $sTimezone;
	}

	/**
	 * Format the date/time value as a string in completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST)
	 *
	 * @param mixed $mDate
	 * @param boolean $bWithTime
	 * @return string
	 */
	public static function full($mDate = null, $bWithTime = false)
	{
		return (new IntlDateFormatter(self::$sLocale, IntlDateFormatter::FULL, self::getTimeType($bWithTime), self::$sTimezone))
			->format(self::getDate($mDate));
	}

	/**
	 *
	 * @param mixed $mDate
	 * @param boolean $bWithTime
	 * @return string
	 */
	public static function long($mDate = null, $bWithTime = false)
	{
		return (new IntlDateFormatter(self::$sLocale, IntlDateFormatter::LONG, self::getTimeType($bWithTime), self::$sTimezone))
			->format(self::getDate($mDate));
	}

	/**
	 *
	 * @param mixed $mDate
	 * @param boolean $bWithTime
	 * @return string
	 */
	public static function medium($mDate = null, $bWithTime = false)
	{
		return (new IntlDateFormatter(self::$sLocale, IntlDateFormatter::MEDIUM, self::getTimeType($bWithTime), self::$sTimezone))
			->format(self::getDate($mDate));
	}

	/**
	 *
	 * @param mixed $mDate
	 * @param boolean $bWithTime
	 * @return string
	 */
	public static function short($mDate = null, $bWithTime = false)
	{
		return (new IntlDateFormatter(self::$sLocale, IntlDateFormatter::SHORT, self::getTimeType($bWithTime), self::$sTimezone))
			->format(self::getDate($mDate));
	}

	/**
	 *
	 * @param unknown $mDate
	 * @return Ambigous <\Carbon\Carbon, \Carbon\Carbon>|\DateTime|\Carbon\Carbon
	 */
	protected static function getDate($mDate)
	{
		if (null === $mDate) {
			return Carbon::now(self::$sTimezone);
		}
		elseif ($mDate instanceof DateTime) {
			return $mDate;
		}
		elseif (Utilities::isInt($mDate)) {
			return Carbon::createFromTimestamp($mDate, self::$sTimezone);
		}
		else {
			return Carbon::parse($mDate, self::$sTimezone);
		}
	}

	/**
	 *
	 * @param boolean $bWithTime
	 * @return number|string
	 */
	protected static function getTimeType($bWithTime = false)
	{
		if ($bWithTime) {
			return IntlDateFormatter::SHORT;
		}

		return IntlDateFormatter::NONE;
	}
}
