<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Intl\Intl;

use MobileCart\CoreBundle\Form\CheckoutBillingAddressType;
use MobileCart\CoreBundle\Constants\CheckoutConstants;

/**
 * Class CheckoutBillingAddressForm
 * @package MobileCart\CoreBundle\EventListener\Checkout
 *
 * Event Listener for building the billing address form in the Checkout
 *
 */
class CheckoutBillingAddressForm
{
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
     * @return bool
     */
    public function getDisplayEmailInput()
    {
        return $this->getCheckoutSessionService()->getAllowGuestCheckout();
    }

    /**
     * @param Event $event
     * @return bool
     */
    public function onCheckoutForm(Event $event)
    {
        if ($event->getSingleStep()
            && $event->getSingleStep() != CheckoutConstants::STEP_BILLING_ADDRESS) {

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

        $formType = new CheckoutBillingAddressType();
        $formType->setCountries($countries);

        $billingFields = [];
        if ($this->getDisplayEmailInput()) {
            $billingFields[] = 'email';
        } else {
            // todo
            //$form->remove('email');
        }

        $billingFields = array_merge($billingFields,
            [
                'billing_name',
                'billing_company',
                'billing_street',
                'billing_street2',
                'billing_city',
                'billing_region',
                'billing_postcode',
                'billing_country_id',
                'billing_phone',
            ]
        );

        $sections = array_merge([
            CheckoutConstants::STEP_BILLING_ADDRESS => [
                'order' => 10,
                'label' => 'Billing Address',
                'fields' => $billingFields,
                'post_url' => $this->getRouter()->generate('cart_checkout_update_billing_address', []),
            ]
        ], $sections);

        $returnData['sections'] = $sections;

        $event->setBillingAddressForm($formType)
            ->setReturnData($returnData);

    }
}
