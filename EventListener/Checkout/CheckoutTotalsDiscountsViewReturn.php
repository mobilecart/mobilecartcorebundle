<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class CheckoutTotalsDiscountsViewReturn
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutTotalsDiscountsViewReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var \MobileCart\CoreBundle\Service\CheckoutSessionService
     */
    protected $checkoutSessionService;

    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var string
     */
    protected $layout = 'frontend';

    /**
     * @var string
     */
    protected $defaultTemplate = 'Checkout:totals_discounts.html.twig';

    /**
     * @param $tpl
     * @return $this
     */
    public function setDefaultTemplate($tpl)
    {
        $this->defaultTemplate = $tpl;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultTemplate()
    {
        return $this->defaultTemplate;
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
     * @param $checkoutSessionService
     * @return $this
     */
    public function setCheckoutSessionService($checkoutSessionService)
    {
        $this->checkoutSessionService = $checkoutSessionService;
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
     * @return \MobileCart\CoreBundle\Service\CartSessionService
     */
    public function getCartSession()
    {
        return $this->getCheckoutSessionService()->getCartSessionService();
    }

    /**
     * @param $layout
     * @return $this
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCheckoutTotalsDiscounts(CoreEvent $event)
    {
        $returnData = $event->getReturnData();
        $request = $event->getRequest();

        $returnData['cart'] = $this->getCartSession()
            ->collectTotals()
            ->getCart();

        $returnData['is_shipping_enabled'] = $this->getCartSession()
            ->getShippingService()
            ->getIsShippingEnabled();

        $returnData['is_multi_shipping_enabled'] = $this->getCartSession()
            ->getShippingService()
            ->getIsMultiShippingEnabled();

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

        $template = $event->getTemplate()
            ? $event->getTemplate()
            : $this->defaultTemplate;

        $response = $this->getThemeService()
            ->render($this->getLayout(), $template, $returnData);

        $event->setResponse($response)
            ->setReturnData($returnData);
    }
}
