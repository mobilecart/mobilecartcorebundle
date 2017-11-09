<?php

namespace MobileCart\CoreBundle\EventListener\Home;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ViewReturn
 * @package MobileCart\CoreBundle\EventListener\Home
 */
class ViewReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\SearchServiceInterface
     */
    protected $searchService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @param $themeService
     * @return $this
     */
    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ThemeService
     */
    public function getThemeService()
    {
        return $this->themeService;
    }

    /**
     * @param \MobileCart\CoreBundle\Service\SearchServiceInterface $search
     * @return $this
     */
    public function setSearchService(\MobileCart\CoreBundle\Service\SearchServiceInterface $search)
    {
        $this->searchService = $search;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\SearchServiceInterface
     */
    public function getSearchService()
    {
        return $this->searchService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onHomeViewReturn(CoreEvent $event)
    {
        $event->setReturnData('search', $this->getSearchService());
        $event->setResponse($this->getThemeService()->render(
            'frontend',
            'Home:index.html.twig',
            $event->getReturnData()
        ));
    }
}
