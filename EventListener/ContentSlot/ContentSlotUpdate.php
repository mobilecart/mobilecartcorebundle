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
    public function onContentSlotUpdate(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $formData = $event->getFormData();
        if (isset($formData['parent_id'])) {
            $parentId = $formData['parent_id'];
            $content = $this->getEntityService()->find(EntityConstants::CONTENT, $parentId);
            if ($content) {
                $entity->setParent($content);
            }
        }

        $this->getEntityService()->persist($entity);
    }
}
