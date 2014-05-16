<?php
/**
 * @ingroup okt_module_diary
 * @brief
 *
 */
use Okatea\Tao\Misc\MonthlyCalendar;

class DiaryMonthlyCalendar extends MonthlyCalendar
{

	protected $aDatesEvents;

	public function setDatesEvents($aDatesEvents)
	{
		$this->aDatesEvents = $aDatesEvents;
	}

	/**
	 * Retourne le HTML additionnel d'un jour dans le calendrier.
	 *
	 * @return string
	 */
	protected function getDayContent()
	{
		# si on as pas d'évènement pour cette date, on passe à la suivante
		if (! isset($this->aDatesEvents[$this->iDate]))
		{
			return null;
		}
		
		# si on est pas sur un vrai jour on passe à la suivante
		if (! $this->bRealDay)
		{
			return null;
		}
		
		$aEvents = array();
		
		foreach ($this->aDatesEvents[$this->iDate] as $aEvent)
		{
			$aEvents[] = sprintf($this->aConfig['htmlEventItem'], html::escapeHTML($aEvent['title']), $aEvent['url'], 'disponibility_' . $aEvent['disponibility'], ! empty($aEvent['color']) ? ' style="background-color: #' . $aEvent['color'] . ' "' : '');
		}
		
		return sprintf($this->aConfig['htmlEventsList'], implode('', $aEvents));
	}
}
