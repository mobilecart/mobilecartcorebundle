<?php

namespace MobileCart\CoreBundle\EventListener\ConfigSetting;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ConfigSettingInsert
 * @package MobileCart\CoreBundle\EventListener\ConfigSetting
 */
class ConfigSettingInsert
{
    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

    /**
     * @param \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     * @return $this
     */
    public function setEntityService(\MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface $entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onConfigSettingInsert(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $this->getEntityService()->persist($entity);
        $event->addSuccessMessage('Config Setting Created!');
    }
}
