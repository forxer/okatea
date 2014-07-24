<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Okatea\Tao\Extensions\Manage\Component;

use Symfony\Component\Filesystem\Filesystem as BaseFilesystem;

/**
 * Wrapper class for Symfony\Component\Filesystem methods to return true or false
 * thus they can be used into Okatea\Tao\Html\CheckList instances.
 */
class Filesystem extends BaseFilesystem
{

	public function copy($originFile, $targetFile, $override = false)
	{
		try
		{
			parent::copy($originFile, $targetFile, $override);
			return true;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	public function mirror($originDir, $targetDir, \Traversable $iterator = null, $options = array())
	{
		try
		{
			parent::mirror($originDir, $targetDir, $iterator, $options);
			return true;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	public function remove($files)
	{
		try
		{
			parent::remove($files);
			return true;
		}
		catch (\Exception $e)
		{
			return false;
		}
	}
}
