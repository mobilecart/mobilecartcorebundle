<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class CustomerOrdersReturn
 * @package MobileCart\CoreBundle\EventListener\Customer
 */
class CustomerOrdersReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

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
    public function onCustomerOrdersReturn(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $customer = $event->getCustomer();
        $typeSections = [];
        $returnData['template_sections'] = $typeSections;

        $orders = $this->getEntityService()->findBy(EntityConstants::ORDER,[
            'customer' => $customer->getId(),
        ]);

        $returnData['orders'] = $orders;

        $response = $this->getThemeService()
            ->render('frontend', 'Customer:orders.html.twig', $returnData);

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
