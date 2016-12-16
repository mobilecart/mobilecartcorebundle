<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\EventDispatcher\Event;

class CheckoutTotalsDiscountsViewReturn
{
    protected $themeService;

    protected $checkoutSessionService;

    protected $entityService;

    protected $layout = 'frontend';

    protected $defaultTemplate = 'Checkout:totals_discounts.html.twig';

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

    public function setDefaultTemplate($tpl)
    {
        $this->defaultTemplate = $tpl;
        return $this;
    }

    public function getDefaultTemplate()
    {
        return $this->defaultTemplate;
    }

    public function getTemplate()
    {
        return $this->getEvent()->getTemplate()
            ? $this->getEvent()->getTemplate()
            : $this->defaultTemplate;
    }

    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

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
     * @return mixed
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    public function setCheckoutSessionService($checkoutSessionService)
    {
        $this->checkoutSessionService = $checkoutSessionService;
        return $this;
    }

    public function getCheckoutSessionService()
    {
        return $this->checkoutSessionService;
    }

    public function getCartSession()
    {
        return $this->getCheckoutSessionService()->getCartSessionService();
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    public function getLayout()
    {
        return $this->layout;
    }

    public function onCheckoutTotalsDiscounts(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();
        $request = $event->getRequest();

        $returnData['cart'] = $this->getCartSession()
            ->collectShippingMethods()
            ->collectTotals()
            ->getCart();

        $returnData['is_shipping_enabled'] = $this->getCartSession()
            ->getShippingService()
            ->getIsShippingEnabled();

        if (!$this->getCartSession()->getCartService()->getIsSpaEnabled()
            && !$request->get('reload', 0)
        ) {
            $this->setDefaultTemplate('Checkout:totals_discounts_full.html.twig');
            $returnData['section'] = $event->getSingleStep();
            $returnData['step_number'] = $event->getStepNumber();
        }

        $addressOptions = [];
        if ($this->getCartSession()->getCustomer()->getId()) {
            $customer = $this->getCartSession()->getCustomer();

            $addresses = $this->getEntityService()->findBy(EntityConstants::CUSTOMER_ADDRESS, [
                'customer' => $customer->getId()
            ]);

            if ($addresses) {

                if (strlen($customer->getStreet()) > 2) {
                    $label = "{$customer->getStreet()} {$customer->getCity()}, {$customer->getRegion()}";
                    $addressOptions[] = [
                        'value' => 'main',
                        'label' => $label,
                    ];
                }

                foreach($addresses as $address) {
                    $label = "{$address->getStreet()} {$address->getCity()}, {$address->getRegion()}";
                    $addressOptions[] = [
                        'value' => $address->getId(),
                        'label' => $label,
                    ];
                }
            }
        }

        $returnData['addresses'] = $addressOptions;

        $response = $this->getThemeService()
            ->render($this->getLayout(), $this->getTemplate(), $returnData);

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
