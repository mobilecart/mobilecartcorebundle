<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Constants\CheckoutConstants;
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
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    protected $layout = 'frontend';

    /**
     * @var string
     */
    protected $defaultTemplate = 'Checkout:totals_discounts.html.twig';

    /**
     * @var \MobileCart\CoreBundle\Service\OrderService
     */
    protected $orderService;

    /**
     * @param $orderService
     * @return $this
     */
    public function setOrderService($orderService)
    {
        $this->orderService = $orderService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\OrderService
     */
    public function getOrderService()
    {
        return $this->orderService;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->getOrderService()->getCartService();
    }

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
     * @param \MobileCart\CoreBundle\Service\ThemeService $themeService
     * @return $this
     */
    public function setThemeService(\MobileCart\CoreBundle\Service\ThemeService $themeService)
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
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @return $this
     */
    public function setRouter(\Symfony\Component\Routing\RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return \Symfony\Component\Routing\RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
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
    public function onCheckoutForm(CoreEvent $event)
    {
        if ($event->get('step_number', 0) > 0) {
            $event->set('step_number', $event->get('step_number') + 1);
        } else {
            $event->set('step_number', 1);
        }

        $customerId = $this->getCartService()->getCustomerId();
        $addressOptions = [];
        if ($customerId) {

            $customer = $this->getCartService()->getCustomer();

            $addresses = $this->getEntityService()->findBy(EntityConstants::CUSTOMER_ADDRESS, [
                'customer' => $customerId
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

        $event->setReturnData('addresses', $addressOptions);

        $javascripts = $event->getReturnData('javascripts', []);

        $tplPath = $this->getThemeService()->getTemplatePath($this->getThemeService()->getThemeConfig()->getFrontendTheme());

        $sectionData = [
            'section' => CheckoutConstants::STEP_TOTALS_DISCOUNTS,
            'template' => $tplPath . 'Checkout:totals_discounts.html.twig',
            'step_number' => $event->get('step_number'),
            'label' => 'Totals and Discounts',
            'post_url' => $this->getRouter()->generate('cart_checkout_update_section', ['section' => CheckoutConstants::STEP_TOTALS_DISCOUNTS]),
            'addresses' => $addressOptions,
            'is_shipping_enabled' => $this->getCartService()->getShippingService()->getIsShippingEnabled(),
            'is_multi_shipping_enabled' => $this->getCartService()->getShippingService()->getIsMultiShippingEnabled(),
            'cart' => $this->getCartService()->collectTotals()->getCart(),
        ];

        if ($event->get('single_step', '')) {
            if ($event->get('single_step', '') == CheckoutConstants::STEP_TOTALS_DISCOUNTS) {
                if ($event->getRequest()->get('ajax', '')) {

                    $template = $event->getTemplate()
                        ? $event->getTemplate()
                        : 'Checkout:totals_discounts.html.twig';

                    $event->setResponse($this->getThemeService()->render(
                        'frontend',
                        $template,
                        $sectionData
                    ));

                } else {
                    $template = $event->getTemplate()
                        ? $event->getTemplate()
                        : 'Checkout:section_full.html.twig';

                    $javascripts[] = [
                        'js_template' => $tplPath . 'Checkout:section_full_js.html.twig',
                    ];

                    $sectionData['javascripts'] = $javascripts;

                    $event->setResponse($this->getThemeService()->render(
                        'frontend',
                        $template,
                        $sectionData
                    ));
                }
            }
        } else {

            if (!$event->getRequest()
                || $event->getRequest()->getMethod() !== 'POST'
            ) {

                $javascripts[] = [
                    'js_template' => $tplPath . 'Checkout:totals_discounts_js.html.twig',
                    'data' => $sectionData,
                ];

                $event->setReturnData('javascripts', $javascripts);
            }

            $sections = $event->getReturnData('sections', []);
            $sections[CheckoutConstants::STEP_TOTALS_DISCOUNTS] = $sectionData;
            $event->setReturnData('sections', $sections);
        }
    }
}
