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

    protected $checkoutSessionService;

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

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
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

    public function setCheckoutSessionService($checkoutSession)
    {
        $this->checkoutSessionService = $checkoutSession;
        return $this;
    }

    public function getCheckoutSessionService()
    {
        return $this->checkoutSessionService;
    }

    public function getDisplayEmailInput()
    {
        return $this->getCheckoutSessionService()->getAllowGuestCheckout();
    }

    public function onCheckoutForm(Event $event)
    {
        if ($event->getSingleStep()
            && $event->getSingleStep() != CheckoutConstants::STEP_BILLING_ADDRESS) {

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

        $formType = new CheckoutBillingAddressType();
        $formType->setCountries($countries);

        $billingFields = [];
        if ($this->getDisplayEmailInput()) {
            $billingFields[] = 'email';
        } else {
            // todo
            //$form->remove('email');
        }

        $billingFields += [
            'billing_name',
            'billing_street',
            'billing_city',
            'billing_region',
            'billing_postcode',
            'billing_country_id',
        ];

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
