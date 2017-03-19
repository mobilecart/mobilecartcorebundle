<?php

namespace MobileCart\CoreBundle\EventListener\UrlRewrite;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class UrlRewriteDelete
 * @package MobileCart\CoreBundle\EventListener\UrlRewrite
 */
class UrlRewriteDelete
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @param $event
     * @return $this
     */
    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    protected function getEvent()
    {
        return $this->event;
    }

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
     * @param Event $event
     */
    public function onUrlRewriteDelete(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();
        $this->getEntityService()->remove($entity, EntityConstants::URL_REWRITE);

        if ($entity && $event->getRequest()->getSession()) {
            $event->getRequest()->getSession()->getFlashBag()->add(
                'success',
                'URL Rewrite Deleted!'
            );
        }

        $event->setReturnData($returnData);
    }
}
