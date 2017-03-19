<?php

namespace MobileCart\CoreBundle\EventListener\UrlRewrite;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class UrlRewriteUpdate
 * @package MobileCart\CoreBundle\EventListener\UrlRewrite
 */
class UrlRewriteUpdate
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
    public function onUrlRewriteUpdate(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $entity = $event->getEntity();
        $this->getEntityService()->persist($entity);

        if ($entity && $event->getRequest()->getSession()) {
            $event->getRequest()->getSession()->getFlashBag()->add(
                'success',
                'URL Rewrite Updated!'
            );
        }

        $event->setReturnData($returnData);
    }
}
