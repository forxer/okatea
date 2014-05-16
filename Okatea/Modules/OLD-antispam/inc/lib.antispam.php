<?php

/**
 * @ingroup okt_module_antispam
 * @brief La classe statique du module antispam
 *
 */
class oktAntispam
{

	public static $filters;

	public static function initFilters()
	{
		global $okt;
		
		if (! isset($okt->spamfilters))
		{
			return;
		}
		
		self::$filters = new oktSpamFilters($okt);
		self::$filters->init($okt->spamfilters);
	}

	public static function isSpam($type, $author, $email, $site, $ip, $content)
	{
		self::initFilters();
		return self::$filters->isSpam($type, $author, $email, $site, $ip, $content);
	}

	public static function trainFilters(&$cur, &$rs)
	{
		$status = null;
		# From ham to spam
		if ($rs->status != - 2 && $cur->status == - 2)
		{
			$status = 'spam';
		}
		
		# From spam to ham
		if ($rs->status == - 2 && $cur->status == 1)
		{
			$status = 'ham';
		}
		
		# the status of this comment has changed
		if ($status)
		{
			$filter_name = $rs->exists('spam_filter') ? $rs->spam_filter : null;
			
			self::initFilters();
			self::$filters->trainFilters($rs, $status, $filter_name);
		}
	}

	public static function statusMessage(&$rs)
	{
		if ($rs->exists('spam_status') && $rs->spam_status !== false)
		{
			$filter_name = $rs->exists('spam_filter') ? $rs->spam_filter : null;
			
			self::initFilters();
			
			return '<p><strong>' . __('m_antispam_This_item_is_a_spam') . '</strong> ' . self::$filters->statusMessage($rs, $filter_name) . '</p>';
		}
	}

	public static function purgeOldSpam(&$okt)
	{
		$defaultDateLastPurge = time();
		$defaultModerationTTL = '7';
		$init = false;
		
		// settings
		$okt->blog->settings->setNameSpace('antispam');
		
		$dateLastPurge = $okt->blog->settings->antispam_date_last_purge;
		if ($dateLastPurge === null)
		{
			$init = true;
			$okt->blog->settings->put('antispam_date_last_purge', $defaultDateLastPurge, 'integer', 'Antispam Date Last Purge (unix timestamp)', true, false);
			$dateLastPurge = $defaultDateLastPurge;
		}
		$moderationTTL = $okt->blog->settings->antispam_moderation_ttl;
		if ($moderationTTL === null)
		{
			$okt->blog->settings->put('antispam_moderation_ttl', $defaultModerationTTL, 'integer', 'Antispam Moderation TTL (days)', true, false);
			$moderationTTL = $defaultModerationTTL;
		}
		
		if ($moderationTTL < 0)
		{
			// disabled
			return;
		}
		
		// we call the purge every day
		if ((time() - $dateLastPurge) > (86400))
		{
			// update dateLastPurge
			if (! $init)
			{
				$okt->blog->settings->put('antispam_date_last_purge', time(), null, null, true, false);
			}
			$date = date('Y-m-d H:i:s', time() - $moderationTTL * 86400);
			oktAntispam::delAllSpam($okt, $date);
		}
	}
}
