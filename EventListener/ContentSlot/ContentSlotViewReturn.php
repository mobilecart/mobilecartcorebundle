<?php

namespace MobileCart\CoreBundle\EventListener\ContentSlot;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ContentSlotViewReturn
 * @package MobileCart\CoreBundle\EventListener\ContentSlot
 */
class ContentSlotViewReturn
{
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
     * @param CoreEvent $event
     */
    public function onContentSlotViewReturn(CoreEvent $event)
    {

        $returnData = $event->getReturnData();

        // nothing for now

        $event->setReturnData($returnData);
    }
}
