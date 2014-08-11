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
use DateTimeZone;
use IntlDateFormatter;
use Okatea\Tao\Misc\Utilities;

class Date extends Carbon
{
	protected static $sLocale = 'en';

	protected static $sTimezone = 'UTC';

	protected static $aTz;

	/**
	 * Define locale to use.
	 *
	 * @param string $sLocale
	 */
	public static function setUserLocale($sLocale)
	{
		self::$sLocale = $sLocale;
	}

	/**
	 * Define timezone to use.
	 *
	 * @param string $sTimezone
	 */
	public static function setUserTimezone($sTimezone)
	{
		self::$sTimezone = $sTimezone;
	}

	public function toMysqlString()
	{
		return $this->format('Y-m-d H:i:s');
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
		return (new IntlDateFormatter(self::$sLocale, IntlDateFormatter::FULL, self::getTimeType($bWithTime), self::$sTimezone))->format(self::getDate($mDate));
	}

	/**
	 * Format the date/time value as a string in long style (January 12, 1952 or 3:30:32pm)
	 *
	 * @param mixed $mDate
	 * @param boolean $bWithTime
	 * @return string
	 */
	public static function long($mDate = null, $bWithTime = false)
	{
		return (new IntlDateFormatter(self::$sLocale, IntlDateFormatter::LONG, self::getTimeType($bWithTime), self::$sTimezone))->format(self::getDate($mDate));
	}

	/**
	 * Format the date/time value as a string in medium style (Jan 12, 1952)
	 *
	 * @param mixed $mDate
	 * @param boolean $bWithTime
	 * @return string
	 */
	public static function medium($mDate = null, $bWithTime = false)
	{
		return (new IntlDateFormatter(self::$sLocale, IntlDateFormatter::MEDIUM, self::getTimeType($bWithTime), self::$sTimezone))->format(self::getDate($mDate));
	}

	/**
	 * Format the date/time value as a string in most abbreviated style,
	 * only essential data (12/13/52 or 3:30pm)
	 *
	 * @param mixed $mDate
	 * @param boolean $bWithTime
	 * @return string
	 */
	public static function short($mDate = null, $bWithTime = false)
	{
		return (new IntlDateFormatter(self::$sLocale, IntlDateFormatter::SHORT, self::getTimeType($bWithTime), self::$sTimezone))->format(self::getDate($mDate));
	}

	/**
	 * Returns an array of supported timezones, codes are keys and names are values.
	 *
	 * @param boolean $bFlip
	 *        	are keys and codes are values
	 * @param boolean $bGroups
	 *        	timezones in arrays of continents
	 * @return array
	 */
	public static function getTimezonesList($bFlip = false, $bGroups = false)
	{
		if (null === self::$aTz)
		{
			self::$aTz = require __DIR__ . '/Timezones.php';

			foreach (self::$aTz as $sTz) {
				self::$aTz[$sTz] = str_replace('_', ' ', $sTz);
			}
		}

		$res = self::$aTz;

		if ($bFlip) {
			$res = array_flip($res);
		}

		if ($bGroups)
		{
			$tmp = [];

			foreach ($res as $k => $v)
			{
				$g = explode('/', $k);
				$tmp[$g[0]][$k] = $v;
			}

			$res = $tmp;
		}

		return $res;
	}

	/**
	 *
	 * @param mixed $mDate
	 * @return Carbon\Carbon>|\DateTime
	 */
	protected static function getDate($mDate)
	{
		if (null === $mDate) {
			return parent::now()->setTimezone(new DateTimeZone(self::$sTimezone));
		}
		elseif ($mDate instanceof DateTime) {
			return $mDate->setTimezone(new DateTimeZone(self::$sTimezone));
		}
		elseif (Utilities::isInt($mDate)) {
			return parent::createFromTimestamp($mDate)->setTimezone(new DateTimeZone(self::$sTimezone));
		}
		else {
			return parent::parse($mDate)->setTimezone(new DateTimeZone(self::$sTimezone));
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
