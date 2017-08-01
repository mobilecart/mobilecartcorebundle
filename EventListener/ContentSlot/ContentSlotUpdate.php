<?php

namespace MobileCart\CoreBundle\EventListener\ContentSlot;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ContentSlotUpdate
 * @package MobileCart\CoreBundle\EventListener\ContentSlot
 */
class ContentSlotUpdate
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @param $entityService
     * @return $this
     */
    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onContentSlotUpdate(CoreEvent $event)
    {
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();
        $formData = $event->getFormData();
        $request = $event->getRequest();

        if (isset($formData['parent_id'])) {
            $parentId = $formData['parent_id'];
            $content = $this->getEntityService()->find(EntityConstants::CONTENT, $parentId);
            if ($content) {
                $entity->setParent($content);
            }
        }

        $this->getEntityService()->persist($entity);
        $event->setReturnData($returnData);
    }
}
