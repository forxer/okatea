<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Modules\Manage\Component;

use Symfony\Component\Filesystem\Filesystem as BaseFilesystem;

/**
 * Wrapper class for Symfony\Component\Filesystem methods to return true or false
 * thus they can be used into Tao\Html\CheckList instances.
 *
 */
class Filesystem extends BaseFilesystem
{
	public function copy($originFile, $targetFile, $override = false)
	{
		try {
			parent::copy($originFile, $targetFile, $override);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
}
