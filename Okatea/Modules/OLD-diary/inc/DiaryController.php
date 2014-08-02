<?php
/**
 * @ingroup okt_module_diary
 * @brief Controller public.
 *
 */
use Okatea\Tao\Html\Modifiers;
use Okatea\Tao\Misc\Utilities;
use Okatea\Website\Controller;

class DiaryController extends Controller
{

	/**
	 * Affichage du calendrier
	 */
	public function diaryList()
	{
		# module actuel
		$this->page->module = 'diary';
		$this->page->action = 'list';
		
		# année et mois à afficher ?
		$iYear = ! empty($_GET['year']) ? intval($_GET['year']) : null;
		$iMonth = ! empty($_GET['month']) ? intval($_GET['month']) : null;
		
		if ($this->okt['request']->attributes->has('date'))
		{
			$aDate = explode('/', $this->okt['request']->attributes->get('date'));
			
			$iYear = ! empty($aDate[0]) ? intval($aDate[0]) : null;
			$iMonth = ! empty($aDate[1]) ? intval($aDate[1]) : null;
			
			unset($aDate);
		}
		
		# initialisation calendrier
		$this->oCalendar = new DiaryMonthlyCalendar(array(
			'htmlBlock' => '<table id="diary" class="common calendar" summary="' . __('agenda') . '">%s</table>',
			
			'htmlNavigation' => '<caption>%2$s - <strong>%1$s</strong> - %3$s</caption>
				<colgroup>
				<col width="14%%"></col>
				<col width="14%%"></col>
				<col width="14%%"></col>
				<col width="14%%"></col>
				<col width="14%%"></col>
				<col width="14%%"></col>
				<col width="14%%"></col>
				</colgroup>',
			
			'htmlPrevLink' => '<a id="diary-prev-link" href="%1$s">&laquo; %2$s</a>',
			'htmlNextLink' => '<a id="diary-next-link" href="%1$s">%2$s &raquo;</a>',
			
			'htmlHeadCelContent' => '%2$s',
			
			'htmlBodyCelContent' => '<h4 class="number">%1$s</h4>',
			
			'htmlClassActive' => 'active',
			'htmlClassDisabled' => 'disabled',
			
			'htmlEventsList' => '<ul class="events-list">%s</ul>',
			'htmlEventItem' => '<li class="%3$s"%4$s><a href="%2$s">%1$s</a></li>',
			
			'urlBase' => DiaryHelpers::getDiaryUrl(),
			'urlPattern' => '/%s/%s'
		), $iYear, $iMonth);
		
		# récupération des évènements pour le mois affiché par le calendrier
		$aDatesEvents = $this->okt->diary->getDatesEventsByInterval($this->oCalendar->getStartDate(), $this->oCalendar->getEndDate(), 1);
		$this->oCalendar->setDatesEvents($aDatesEvents);
		
		# meta description
		if (! empty($this->okt->diary->config->meta_description[$this->okt->user->language]))
		{
			$this->page->meta_description = $this->okt->diary->config->meta_description[$this->okt->user->language];
		}
		else
		{
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}
		
		# meta keywords
		if (! empty($this->okt->diary->config->meta_keywords[$this->okt->user->language]))
		{
			$this->page->meta_keywords = $this->okt->diary->config->meta_keywords[$this->okt->user->language];
		}
		else
		{
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}
		
		# fil d'ariane
		if (! $this->isHomePageRoute())
		{
			$this->page->breadcrumb->add($this->okt->diary->getName(), DiaryHelpers::getDiaryUrl());
		}
		
		# title tag du module
		$this->page->addTitleTag($this->okt->diary->getTitle());
		
		# titre de la page
		$this->page->setTitle($this->okt->diary->getName());
		
		# titre SEO de la page
		$this->page->setTitleSeo($this->okt->diary->getNameSeo());
		
		# affichage du template
		return $this->render('diary_list_tpl', array(
			'oCal' => $this->oCalendar
		));
	}

	/**
	 * Affichage d'un évènement
	 */
	public function diaryEvent()
	{
		# module actuel
		$this->page->module = 'diary';
		$this->page->action = 'event';
		
		# récupération de l'élément en fonction du slug
		if (! $slug = $this->okt['request']->attributes->get('slug'))
		{
			return $this->serve404();
		}
		
		# récupération de l'évènement
		$rsEvent = $this->okt->diary->getEvents(array(
			'slug' => $slug,
			'visibility' => 1
		));
		
		if ($rsEvent->isEmpty())
		{
			return $this->serve404();
		}
		
		# meta description
		if (! empty($rsEvent->meta_description))
		{
			$this->page->meta_description = $rsEvent->meta_description;
		}
		elseif (! empty($this->okt->diary->config->meta_description[$this->okt->user->language]))
		{
			$this->page->meta_description = $this->okt->diary->config->meta_description[$this->okt->user->language];
		}
		else
		{
			$this->page->meta_description = $this->page->getSiteMetaDesc();
		}
		
		# meta keywords
		if (! empty($rsEvent->meta_keywords))
		{
			$this->page->meta_keywords = $rsEvent->meta_keywords;
		}
		elseif (! empty($this->okt->diary->config->meta_keywords[$this->okt->user->language]))
		{
			$this->page->meta_keywords = $this->okt->diary->config->meta_keywords[$this->okt->user->language];
		}
		else
		{
			$this->page->meta_keywords = $this->page->getSiteMetaKeywords();
		}
		
		# description
		if (! $this->okt->diary->config->enable_rte)
		{
			$rsEvent->description = Modifiers::nlToP($rsEvent->description);
		}
		
		# récupération des images
		$rsEvent->images = $rsEvent->getImagesInfo();
		
		# récupération des fichiers
		$rsEvent->files = $rsEvent->getFilesInfo();
		
		# title tag du module
		$this->page->addTitleTag($this->okt->diary->getTitle());
		
		# fil d'ariane
		if (! $this->isHomePageRoute())
		{
			$this->page->breadcrumb->add($this->okt->diary->getName(), DiaryHelpers::getDiaryUrl());
			
			$this->page->breadcrumb->add($rsEvent->title, $rsEvent->getEventUrl());
		}
		
		# title tag
		$this->page->addTitleTag((! empty($rsEvent->title_tag) ? $rsEvent->title_tag : $rsEvent->title));
		
		# titre de la page
		$this->page->setTitle($rsEvent->title);
		
		# titre SEO de la page
		$this->page->setTitleSeo(! empty($rsEvent->title_seo) ? $rsEvent->title_seo : $rsEvent->title);
		
		# affichage du template
		return $this->render('diary_event_tpl', array(
			'rsEvent' => $rsEvent
		));
	}
}
