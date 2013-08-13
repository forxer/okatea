<?php
/**
 * @ingroup okt_module_guestbook
 * @brief La classe d'installation du module guestbook.
 *
 */

class moduleInstall_guestbook extends oktModuleInstall
{
	public function install()
	{
		$this->setDefaultAdminPerms(array(
			'guestbook'
		));
	}

	public function update()
	{
		# si version installée inférieure à 1.6
		if (version_compare($this->okt->guestbook->version(), '1.6', '<'))
		{
			$rsSig = $this->okt->guestbook->getSig();
			while ($rsSig->fetch())
			{
				$this->okt->guestbook->updSig(array(
					'id' 		=> $rsSig->id,
					'language'  => 'fr',
					'message' 	=> $rsSig->message,
					'nom' 		=> $rsSig->nom,
					'email' 	=> $rsSig->email,
					'url' 		=> $rsSig->url,
					'note' 		=> $rsSig->note,
					'visible' 	=> $rsSig->visible
				));
			}
		}
	}

} # class
