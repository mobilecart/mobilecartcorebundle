<?php

namespace MobileCart\CoreBundle\EventListener\WebhookLog;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

class WebhookLogInsert
{
    protected $entityService;

    protected $event;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    protected function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function onWebhookLogInsert(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();
        $request = $event->getRequest();
        $input = $event->getInput();
        $entity = $event->getEntity();

        $service = $event->getService()
            ? $event->getService()
            : '';

        $isProcessed = $event->getIsProcessed()
            ? $event->getIsProcessed()
            : 0;

        $entity->setSourceIp($request->getClientIp())
            ->setRequestBody($input)
            ->setRequestMethod($request->getMethod())
            ->setService($service)
            ->setCreatedAt(new \DateTime('now'))
            ->setIsProcessed($isProcessed);

        $this->getEntityService()->persist($entity);

        $event->setReturnData($returnData);
        $event->setResponse(new JsonResponse($returnData));
    }
}
