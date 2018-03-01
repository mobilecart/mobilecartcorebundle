<?php

namespace MobileCart\CoreBundle\EventListener\ConfigSetting;

use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ConfigSettingUpdate
 * @package MobileCart\CoreBundle\EventListener\ConfigSetting
 */
class ConfigSettingUpdate
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
    public function onConfigSettingUpdate(CoreEvent $event)
    {
        $entity = $event->getEntity();
        try {
            $this->getEntityService()->persist($entity);
            $event->setSuccess(true);
            $event->addSuccessMessage('Config Setting Updated!');
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while saving Config Setting');
        }
    }
}
