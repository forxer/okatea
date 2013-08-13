<?php
/**
 * Session cache driver.
 */
class SessionCache extends AbstractCache
{
    
	/**
	 * {@inheritdoc}
	 */
	public function getIds()
	{
		return array_keys($_SESSION["__cache"]);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _doFetch($id)
	{
		if (isset($_SESSION["__cache"][$id])) {
			return unserialize($_SESSION["__cache"][$id]);
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _doContains($id)
	{
		return isset($_SESSION["__cache"][$id]);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _doSave($id, $data, $lifeTime = 0)
	{
		$_SESSION["__cache"][$id] = serialize($data);

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function _doDelete($id)
	{
		unset($_SESSION["__cache"][$id]);

		return true;
	}
}