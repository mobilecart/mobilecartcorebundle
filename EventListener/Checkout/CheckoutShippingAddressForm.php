<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Intl\Intl;

use MobileCart\CoreBundle\Form\CheckoutShippingAddressType;
use MobileCart\CoreBundle\Constants\CheckoutConstants;

class CheckoutShippingAddressForm
{
    /**
     * @var Event
     */
    protected $event;

    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    protected $checkoutSessionService;

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
     * @param $router
     * @return $this
     */
    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param $checkoutSession
     * @return $this
     */
    public function setCheckoutSessionService($checkoutSession)
    {
        $this->checkoutSessionService = $checkoutSession;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    public function getCheckoutSessionService()
    {
        return $this->checkoutSessionService;
    }

    /**
     * @param Event $event
     * @return bool
     */
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
        $returnData = $event->getReturnData();

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
                    'shipping_company',
                    'shipping_street',
                    'shipping_street2',
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
