<?php

namespace MobileCart\CoreBundle\EventListener\Checkout;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\CheckoutConstants;

/**
 * Class CheckoutShippingAddressForm
 * @package MobileCart\CoreBundle\EventListener\Checkout
 */
class CheckoutShippingAddressForm
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var string
     */
    protected $formTypeClass = '';

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

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
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @return $this
     */
    public function setFormFactory(\Symfony\Component\Form\FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
        return $this;
    }

    /**
     * @return \Symfony\Component\Form\FormFactoryInterface
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @param string $formTypeClass
     * @return $this
     */
    public function setFormTypeClass($formTypeClass)
    {
        $this->formTypeClass = $formTypeClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormTypeClass()
    {
        return $this->formTypeClass;
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
     * @return bool
     */
    public function getIsShippingEnabled()
    {
        return $this->getCartService()->getShippingService()->getIsShippingEnabled();
    }

    /**
     * @param CoreEvent $event
     * @return bool
     */
    public function onCheckoutForm(CoreEvent $event)
    {
        if (!$this->getIsShippingEnabled()) {
            return false;
        }

        if ($event->get('step_number', 0) > 0) {
            $event->set('step_number', $event->get('step_number') + 1);
        } else {
            $event->set('step_number', 1);
        }

        $form = $this->getFormFactory()->create($this->getFormTypeClass(), [], [
            'method' => 'post',
            'action' => $this->getRouter()->generate('cart_checkout_update_section', [
                'section' => CheckoutConstants::STEP_SHIPPING_ADDRESS
            ]),
        ]);

        $shippingFields = [
            'is_shipping_same', // todo : move this to the billing step
            'shipping_name',
            'shipping_company',
            'shipping_street',
            'shipping_street2',
            'shipping_city',
            'shipping_region',
            'shipping_postcode',
            'shipping_country_id',
            'shipping_phone',
        ];

        $javascripts = $event->getReturnData('javascripts', []);

        $tplPath = $this->getThemeService()->getTemplatePath($this->getThemeService()->getThemeConfig()->getFrontendTheme());

        $customer = $this->getCartService()->getCart()->getCustomer();

        foreach($shippingFields as $field) {

            $customerValue = $customer->get($field);

            switch($field) {
                case 'is_shipping_same':
                    // must be a new "feature" in Symfony? It won't take a '1' anymore?
                    $form->get($field)->setData((bool) $customerValue);
                    break;
                case 'shipping_name':
                    if ($customer->get('first_name') && !$customer->get('billing_name')) {
                        $form->get($field)->setData("{$customer->get('first_name')} {$customer->get('last_name')}");
                    } else {
                        $form->get($field)->setData($customerValue);
                    }
                    break;
                default:
                    if (!is_null($customerValue)) {
                        $form->get($field)->setData($customerValue);
                    }
                    break;
            }
        }

        $sectionData = [
            'section' => CheckoutConstants::STEP_SHIPPING_ADDRESS,
            'step_number' => $event->get('step_number'),
            'label' => 'Shipping Address',
            'fields' => $shippingFields,
            'post_url' => $this->getRouter()->generate('cart_checkout_update_section', [
                'section' => CheckoutConstants::STEP_SHIPPING_ADDRESS
            ]),
            'form' => $form,
            'form_view' => $form->createView(),
        ];

        if ($event->get('single_step', '')) {
            if ($event->get('single_step', '') == CheckoutConstants::STEP_SHIPPING_ADDRESS) {

                $template = $event->getTemplate()
                    ? $event->getTemplate()
                    : 'Checkout:section_full.html.twig';

                $javascripts[] = [
                    'js_template' => $tplPath . 'Checkout:section_full_js.html.twig',
                ];

                $sectionData['javascripts'] = $javascripts;

                $event->setResponse($this->getThemeService()->render('frontend', $template, $sectionData));
            }
        } else {

            $isAjax = $event->getRequest()
                ? $event->getRequest()->get('ajax', '') == 1
                : false;

            if (!$isAjax) {

                $javascripts[] = [
                    'js_template' => $tplPath . 'Checkout:section_address_js.html.twig',
                    'data' => $sectionData,
                ];

                $event->setReturnData('javascripts', $javascripts);
            }
        }

        $sections = $event->getReturnData('sections', []);
        $sections[CheckoutConstants::STEP_SHIPPING_ADDRESS] = $sectionData;
        $event->setReturnData('sections', $sections);
    }
}
