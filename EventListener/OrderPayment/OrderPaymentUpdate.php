<?php

namespace MobileCart\CoreBundle\EventListener\OrderPayment;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\CartComponent\Payment;

/**
 * Class OrderPaymentUpdate
 * @package MobileCart\CoreBundle\EventListener\OrderPayment
 */
class OrderPaymentUpdate
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

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
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CurrencyServiceInterface
     */
    public function getCurrencyService()
    {
        return $this->getCartService()->getCartTotalService()->getCurrencyService();
    }

    /**
     * @param CoreEvent $event
     */
    public function onOrderPaymentUpdate(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\OrderPayment $entity */
        $entity = $event->getEntity();
        $order = $entity->getOrder();

        $baseCurrency = $this->getCurrencyService()->getBaseCurrency();
        $currency = $order->getCurrency();
        $entity->setBaseCurrency($baseCurrency);
        $entity->setCurrency($currency);

        $amount = $baseCurrency == $currency
            ? $entity->getBaseAmount()
            : $this->getCurrencyService()->convert($entity->getBaseAmount(), $currency);

        $entity->setAmount($amount);

        try {
            $this->getEntityService()->persist($entity);
            $event->setSuccess(true);
            $event->addSuccessMessage('Payment Updated !');
        } catch(\Exception $e) {
            $event->addErrorMessage('An error occurred while saving Order Payment');
        }
    }
}
