<?php

namespace MobileCart\CoreBundle\EventListener\Customer;

use MobileCart\CoreBundle\Event\CoreEvent;
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
     * @param CoreEvent $event
     */
    public function onCustomerOrdersReturn(CoreEvent $event)
    {
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
