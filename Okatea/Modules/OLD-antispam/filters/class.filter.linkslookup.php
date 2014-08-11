<?php

/**
 * @ingroup okt_module_antispam
 * @brief Filtre links lookup
 *
 */
class oktFilterLinksLookup extends oktSpamFilter
{

	public $name = 'Links Lookup';

	private $server = 'multi.surbl.org';

	protected function setInfo()
	{
		$this->description = __('m_antispam_Checks_links_in_comments');
	}

	public function getStatusMessage($status)
	{
		return sprintf(__('m_antispam_Filtered_by_%1$s_with_server_%2$s'), $this->guiLink(), $status);
	}

	public function isSpam($type, $author, $email, $site, $ip, $content, &$status)
	{
		if (!$ip || long2ip(ip2long($ip)) != $ip)
		{
			return;
		}
		
		$urls = $this->getLinks($content);
		array_unshift($urls, $site);
		
		foreach ($urls as $u)
		{
			$b = parse_url($u);
			if (!isset($b['host']) || !$b['host'])
			{
				continue;
			}
			
			$domain = preg_replace('/^(.*\.)([^.]+\.[^.]+)$/', '$2', $b['host']);
			$host = $domain . '.' . $this->server;
			
			if (gethostbyname($host) != $host)
			{
				$status = substr($domain, 0, 128);
				return true;
			}
		}
	}

	private function getLinks($text)
	{
		$res = [];
		
		# href attribute on "a" tags
		if (preg_match_all('/<a ([^>]+)>/ms', $text, $match, PREG_SET_ORDER))
		{
			for ($i = 0; $i < count($match); $i ++)
			{
				if (preg_match('/href="(http:\/\/[^"]+)"/ms', $match[$i][1], $matches))
				{
					$res[] = $matches[1];
				}
			}
		}
		
		return $res;
	}
}

