<?php

namespace MobileCart\CoreBundle\EventListener\WebhookLog;

use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class WebhookLogInsert
 * @package MobileCart\CoreBundle\EventListener\WebhookLog
 */
class WebhookLogInsert
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
    public function onWebhookLogInsert(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
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
