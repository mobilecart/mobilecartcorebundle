<?php

namespace MobileCart\CoreBundle\EventListener\CustomerAddress;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Intl\Intl;

/**
 * Class CustomerAddressForm
 * @package MobileCart\CoreBundle\EventListener\CustomerAddress
 */
class CustomerAddressForm
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CurrencyService
     */
    protected $currencyService;

    /**
     * @var string
     */
    protected $formTypeClass = '';

    protected $formFactory;

    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

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
     * @param $currencyService
     * @return $this
     */
    public function setCurrencyService($currencyService)
    {
        $this->currencyService = $currencyService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->currencyService;
    }

    /**
     * @param $class
     * @return $this
     */
    public function setFormTypeClass($class)
    {
        $this->formTypeClass = $class;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormTypeClass()
    {
        return $this->formTypeClass;
    }

    public function setFormFactory($formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    public function getFormFactory()
    {
        return $this->formFactory;
    }

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param $cartService
     * @return $this
     */
    public function setCartService($cartService)
    {
        $this->cartService = $cartService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->cartService;
    }

    /**
     * @param Event $event
     */
    public function onCustomerAddressForm(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $form = $this->getFormFactory()->create($this->getFormTypeClass(), $event->getEntity(), [
            'action' => $event->getAction(),
            'method' => $event->getMethod(),
        ]);

        $formSections = [
            'general' => [
                'label' => 'General',
                'id' => 'general',
                'fields' => [
                    'name',
                    'company',
                    'phone',
                    'street',
                    'street2',
                    'city',
                    'region',
                    'postcode',
                    'country_id'
                ],
            ],
        ];

        $returnData['country_regions'] = $this->getCartService()->getCountryRegions();
        $returnData['form_sections'] = $formSections;
        $returnData['form'] = $form;

        $event->setForm($form);
        $event->setReturnData($returnData);
    }
}
