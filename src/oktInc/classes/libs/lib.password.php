<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Require the password compat library
 */
require_once OKT_VENDOR_PATH.'/password_compat/lib/password.php';


/**
 * @class password
 * @ingroup okt_classes_libs
 * @brief password compat wrapper class
 */
class password
{
	public static function hash($password, $algo, array $options = array())
	{
		return password_hash($password, $algo, $options);
	}

	public static function get_info($hash)
	{
		return password_get_info($hash);
	}

	public static function needs_rehash($hash, $algo, array $options = array())
	{
		return password_needs_rehash($hash, $algo, $options);
	}

	public static function verify($password, $hash)
	{
		return password_verify($password, $hash);
	}

} # class
