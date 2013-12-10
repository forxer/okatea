<?php
/*
 * This file is part of Okatea.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tao\Misc;

/**
 * Outil pour la génération d'un calendrier mensuel.
 *
 * Dépendance : Clearbricks class dt Date/time utilities
 *
 * Exemple :
 *
 *	$oCal = new MonthlyCalendar(array(
 *			'urlBase' 		=> 'calendar.php',
 *			'urlPattern' 	=> '?year=%s&amp;month=%s'
 *		),
 *		(!empty($_GET['year']) ? $_GET['year'] : null),
 *		(!empty($_GET['month']) ? $_GET['month'] : null)
 *	);
 *
 * 	echo $oCal->getHtml();
 */
class MonthlyCalendar
{
	protected $aConfig = array(
		'htmlBlock' 			=> '<table class="calendar" summary="calendar">%s</table>',

		'htmlNavigation' 		=> '<caption> %2$s %1$s %3$s</caption>',
		'htmlPrevLink' 			=> '<a href="%1$s" title="%2$s">&laquo;</a>',
		'htmlNextLink' 			=> '<a href="%1$s" title="%2$s">&raquo;</a>',

		'htmlHead' 				=> '<thead>%s</thead>',
		'htmlHeadLine' 			=> '<tr>%s</tr>',
		'htmlHeadCel' 			=> '<th scope="col">%s</th>',
		'htmlHeadCelContent' 	=> '<abbr title="%2$s">%1$s</abbr>',

		'htmlBody' 				=> '<tbody>%s</tbody>',
		'htmlBodyLine' 			=> '<tr>%s</tr>',
		'htmlBodyCel' 			=> '<td class="%2$s" id="day-%3$s">%1$s</td>',
		'htmlBodyCelContent' 	=> '<span class="number">%s</span>',

		'htmlClassActive'		=> 'active',
		'htmlClassDisabled'		=> 'disabled',

		'htmlEmptyCelContent' 	=> '&nbsp;',

		'urlBase' 				=> '/',
		'urlPattern' 			=> '%s/%s/',

		'mondayFirstDay' 		=> true # Monday is TRUE, FALSE is Sunday
	);

	protected $iCurrentDay;
	protected $iCurrentMonth;
	protected $iCurrentYear;

	protected $iDay = 1;
	protected $iMonth;
	protected $iYear;
	protected $iDate;

	protected $iTimestamp;

	protected $iPrevMonth;
	protected $iPrevYear;

	protected $iNextMonth;
	protected $iNextYear;

	protected $iFirstDay;
	protected $iLastDay;

	protected $bRealDay = false;

	const SUNDAY_TS = 1042329600;

	/**
	 * Constructor
	 *
	 * @param array $aConfig
	 * @param integer $year
	 * @param integer $month
	 * @return void
	 */
	public function __construct($aConfig=array(), $year=null, $month=null)
	{
		$this->setConfig($aConfig);

		$this->iCurrentDay = (integer)date('d',time());
		$this->iCurrentMonth = (integer)date('m',time());
		$this->iCurrentYear = (integer)date('Y',time());

		$this->setDate($year,$month);
	}

	/**
	 * Définit la configuration.
	 *
	 * @param array $aConfig
	 * @return void
	 */
	public function setConfig($aConfig)
	{
		$this->aConfig = $aConfig + $this->aConfig;
	}

	/**
	 * Définit la date du mois à afficher.
	 *
	 * @param integer $year
	 * @param integer $month
	 * @return void
	 */
	public function setDate($year=null, $month=null)
	{
		$this->iYear = !is_null($year) ? intval($year) : $this->iCurrentYear;
		$this->iMonth = !is_null($month) ? intval($month) : $this->iCurrentMonth;

		$this->iTimestamp = strtotime($this->iYear.'-'.$this->iMonth.'-01');

/*		$this->iNextMonth = strtotime('next month',$this->iTimestamp);
		$this->iLastMonth = strtotime('last month',$this->iTimestamp);
		debug(date('d-m-Y',$this->iNextMonth));
		debug(date('d-m-Y',$this->iLastMonth));
*/
		$this->defineFirstDay();
		$this->defineLastDay();
	}

	/**
	 * Retourne la date du premier jour du mois affiché.
	 *
	 * @return void
	 */
	public function getStartDate()
	{
		return $this->iYear.'-'.$this->iMonth.'-01';
	}

	/**
	 * Retourne la date du dernier jour du mois affiché.
	 *
	 * @return void
	 */
	public function getEndDate()
	{
		return $this->iYear.'-'.$this->iMonth.'-'.$this->iLastDay;
	}

	/**
	 * Retourne le calendrier sous forme de HTML.
	 *
	 * @return string
	 */
	public function getHtml()
	{
		$sHtml = '';

		$sHtml .= $this->getNavigation();

		$sHtml .= $this->getHead();

		$sHtml .= $this->getBody();

		return sprintf($this->aConfig['htmlBlock'],$sHtml);
	}

	/**
	 * Retourne le HTML des liens de navigation.
	 *
	 * @return string
	 */
	protected function getNavigation()
	{
		$sCurrent = dt::str('%B %Y',$this->iTimestamp);

		return sprintf($this->aConfig['htmlNavigation'],$sCurrent,$this->getPrevLink(),$this->getNextLink());
	}

	/**
	 * Retourne le HTML du lien du mois précédent.
	 *
	 * @return string
	 */
	protected function getPrevLink()
	{
		$this->definePrevDate();

		return sprintf($this->aConfig['htmlPrevLink'],$this->getPrevUrl(),$this->getPrevDate());
	}

	/**
	 * Retourne l'URL du mois précédent.
	 *
	 * @return string
	 */
	protected function getPrevUrl()
	{
		return $this->aConfig['urlBase'].sprintf($this->aConfig['urlPattern'],$this->iPrevYear,$this->iPrevMonth);
	}

	/**
	 * Retourne la date du mois précédent.
	 *
	 * @return string
	 */
	protected function getPrevDate()
	{
		return dt::str('%B %Y',strtotime($this->iPrevYear.'-'.$this->iPrevMonth.'-01'));
	}

	/**
	 * Retourne le HTML du lien du mois suivant.
	 *
	 * @return string
	 */
	protected function getNextLink()
	{
		$this->defineNextDate();

		return sprintf($this->aConfig['htmlNextLink'],$this->getNextUrl(),$this->getNextDate());
	}

	/**
	 * Retourne l'URL du mois suivant.
	 *
	 * @return string
	 */
	protected function getNextUrl()
	{
		return $this->aConfig['urlBase'].sprintf($this->aConfig['urlPattern'],$this->iNextYear,$this->iNextMonth);
	}

	/**
	 * Retourne la date du mois suivant.
	 *
	 * @return string
	 */
	protected function getNextDate()
	{
		return dt::str('%B %Y',strtotime($this->iNextYear.'-'.$this->iNextMonth.'-01'));
	}

	/**
	 * Retourne le HTML de l'en-tête du calendrier.
	 *
	 * @return string
	 */
	protected function getHead()
	{
		$iFirstTs = self::SUNDAY_TS + ((integer)$this->aConfig['mondayFirstDay'] * 86400);
		$iLastTs = $iFirstTs + (6 * 86400);

		$res = '';
		for ($j = $iFirstTs; $j <= $iLastTs; $j = $j+86400) {
			$res .= $this->getHeadCel($j);
		}

		return sprintf($this->aConfig['htmlHead'], sprintf($this->aConfig['htmlHeadLine'], $res));
	}

	/**
	 * Retourne le HTML d'une cellule de l'en-tête du calendrier.
	 *
	 * @param integer $j
	 * @return string
	 */
	protected function getHeadCel($j)
	{
		return sprintf($this->aConfig['htmlHeadCel'],$this->getHeadCelContent($j));
	}

	/**
	 * Retourne le HTML du contenu d'une cellule de l'en-tête du calendrier.
	 *
	 * @param integer $j
	 * @return string
	 */
	protected function getHeadCelContent($j)
	{
		return sprintf($this->aConfig['htmlHeadCelContent'],dt::str('%a',$j),dt::str('%A',$j));
	}

	/**
	 * Retourne le HTML du corps du calendrier.
	 *
	 * @return string
	 */
	protected function getBody()
	{
		$i = 0;
		$sBody = '';
		$sLine = '';

		while ($i<42)
		{
			$this->iDate = date('Y-m-d',strtotime($this->iYear.'-'.$this->iMonth.'-'.$this->iDay));

			if ($i === $this->iFirstDay) {
				$this->bRealDay = true;
			}

			if ($this->bRealDay && !checkdate($this->iMonth, $this->iDay, $this->iYear)) {
				$this->bRealDay = false;
			}

/*
			if (!$this->bRealDay)
			{
				if ($this->iDay === 1) {
					debug('avant:'.$this->iDay);
				}
				else {
					debug('après:'.$this->iDay);
				}
			}
*/

			# cellule du jour
			$sLine .= $this->getBodyCel();

			# fin de semaine
			if (($i+1)%7 == 0)
			{
				$sBody .= sprintf($this->aConfig['htmlBodyLine'],$sLine);

				$sLine = '';

				# fin du mois
				if ($this->iDay >= $this->iLastDay) {
					$i = 42;
				}
			}

			if ($this->bRealDay) {
				$this->iDay++;
			}

			$i++;
		}

		return sprintf($this->aConfig['htmlBody'],$sBody);
	}

	/**
	 * Retourne le HTML complet d'un jour.
	 *
	 * @return string
	 */
	protected function getBodyCel()
	{
		$aClasses = array();

		# date du jour ?
		if ($this->isToday()) {
			$aClasses[] = $this->aConfig['htmlClassActive'];
		}

		# jour inexistant ?
		if (!$this->bRealDay) {
			$aClasses[] = $this->aConfig['htmlClassDisabled'];
		}

		$sCell = $this->getDayNumber();

		$sCell .= $this->getDayContent();

		return sprintf($this->aConfig['htmlBodyCel'], $sCell, implode(' ',$aClasses), $this->iDate);
	}

	/**
	 * Retourne le HTML d'un jour dans le calendrier.
	 *
	 * @return string
	 */
	protected function getDayNumber()
	{
		if ($this->bRealDay) {
			$sDayNumber = sprintf($this->aConfig['htmlBodyCelContent'], $this->iDay);
		}
		else {
			$sDayNumber = $this->aConfig['htmlEmptyCelContent'];
		}

		return $sDayNumber;
	}

	/**
	 * Retourne le HTML additionnel d'un jour dans le calendrier.
	 *
	 * @return string
	 */
	protected function getDayContent()
	{
		return null;
	}

	/**
	 * Détermine la date du mois précédent.
	 *
	 * @return void
	 */
	protected function definePrevDate()
	{
		if ($this->iMonth == 1)
		{
			$this->iPrevMonth = 12;
			$this->iPrevYear = $this->iYear-1;
		}
		else {
			$this->iPrevMonth = $this->iMonth-1;
			$this->iPrevYear = $this->iYear;
		}
	}

	/**
	 * Détermine la date du mois suivant.
	 *
	 * @return void
	 */
	protected function defineNextDate()
	{
		if ($this->iMonth == 12)
		{
			$this->iNextMonth = 1;
			$this->iNextYear = $this->iYear+1;
		}
		else {
			$this->iNextMonth = $this->iMonth+1;
			$this->iNextYear = $this->iYear;
		}
	}

	/**
	 * Détermine le premier vrai jour du mois affiché (dans la première semaine 1 à 7).
	 *
	 * @return void
	 */
	protected function defineFirstDay()
	{
		$iFirstDay = (integer)date('w',$this->iTimestamp);

		$iFirstDay = ($iFirstDay === 0) ? 7 : $iFirstDay;
		$iFirstDay = $iFirstDay - (integer)$this->aConfig['mondayFirstDay'];

		$this->iFirstDay = $iFirstDay;
	}

	/**
	 * Détermine le dernier vrai jour du mois affiché.
	 *
	 * @return void
	 */
	protected function defineLastDay()
	{
		$this->iLastDay = (integer)date('t',$this->iTimestamp);
	}

	/**
	 * Détermine si on est aujourd'hui.
	 *
	 */
	protected function isToday()
	{
		if ($this->bRealDay
			&& ($this->iYear === $this->iCurrentYear)
			&& ($this->iMonth === $this->iCurrentMonth)
			&& ($this->iDay === $this->iCurrentDay))
		{
			return true;
		}

		return false;
	}


}
