<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Intl\Intl;

use MobileCart\CoreBundle\Form\CheckoutShippingAddressType;
use MobileCart\CoreBundle\Constants\CheckoutConstants;

class CheckoutShippingAddressForm
{
    protected $event;

    protected $router;

    protected $checkoutSessionService;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
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

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    public function setCheckoutSessionService($checkoutSession)
    {
        $this->checkoutSessionService = $checkoutSession;
        return $this;
    }

    public function getCheckoutSessionService()
    {
        return $this->checkoutSessionService;
    }

    public function onCheckoutForm(Event $event)
    {
        if ($event->getSingleStep()
            && $event->getSingleStep() != CheckoutConstants::STEP_SHIPPING_ADDRESS) {

            return false;
        }

        if (!$this->getCheckoutSessionService()->getCartSessionService()->getShippingService()->getIsShippingEnabled()) {
            return false;
        }

        $this->setEvent($event);
        $returnData = $this->getReturnData();

        // sections are combined with other listeners/observer
        //  and later ordered
        $sections = isset($returnData['sections'])
            ? $returnData['sections']
            : [];

        $allCountries = Intl::getRegionBundle()->getCountryNames();
        $allowedCountries = $this->getCheckoutSessionService()->getAllowedCountryIds();

        $countries = [];
        foreach($allowedCountries as $countryId) {
            $countries[$countryId] = $allCountries[$countryId];
        }

        $formType = new CheckoutShippingAddressType();
        $formType->setCountries($countries);

        $sections = array_merge([
            CheckoutConstants::STEP_SHIPPING_ADDRESS => [
                'order' => 20,
                'label' => 'Shipping Address',
                'fields' => [
                    'is_shipping_same',
                    'shipping_name',
                    'shipping_street',
                    'shipping_city',
                    'shipping_region',
                    'shipping_postcode',
                    'shipping_country_id',
                    'shipping_phone',
                ],
                'post_url' => $this->getRouter()->generate('cart_checkout_update_shipping_address', []),
            ],
        ], $sections);

        $returnData['sections'] = $sections;

        $event->setShippingAddressForm($formType)
            ->setReturnData($returnData);

    }
}
