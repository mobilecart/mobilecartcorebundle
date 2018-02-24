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
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @param \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     * @return $this
     */
    public function setEntityService(\MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface $entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->entityService;
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
     * @param CoreEvent $event
     */
    public function onOrderPaymentUpdate(CoreEvent $event)
    {
        $entity = $event->getEntity();
        $order = $entity->getOrder();
        $baseCurrency = $this->getCartService()->getCartTotalService()->getCurrencyService()->getBaseCurrency();
        if ($order->getCurrency() == $baseCurrency) {
            $entity->setPrice($entity->getBasePrice());
        } else {
            // todo : currency
        }

        $this->getEntityService()->persist($entity);
        $event->addSuccessMessage('Payment Updated!');

        $formData = $event->getFormData();
        if (isset($formData['adjust_totals']) && $formData['adjust_totals']) {

            // populate cart with json
            $this->getCartService()->initCartJson($order->getJson());

            $payments = $order->getPayments();
            $this->getCartService()->removePayments();

            foreach($payments as $aEntity) {
                // create cart payment from entity
                $payment = new Payment();
                $payment->fromArray($aEntity->getData());
                $this->getCartService()->addPayment($payment);
            }

            $this->getCartService()->collectTotals();

            $baseGrandTotal = $this->getCartService()
                ->getTotal(\MobileCart\CoreBundle\EventListener\Cart\GrandTotal::KEY)
                ->getValue();

            $baseShippingTotal = $this->getCartService()
                ->getTotal(\MobileCart\CoreBundle\EventListener\Cart\PaymentTotal::KEY)
                ->getValue();

            $order->setBaseTotal($baseGrandTotal)
                ->setBaseShippingTotal($baseShippingTotal);

            if ($order->getCurrency() == $baseCurrency) {

                $order->setShippingTotal($baseShippingTotal)
                    ->setTotal($baseGrandTotal);

            } else {
                // todo : currency
            }

            $order->setJson($this->getCartService()->getCart()->toJson());
            $this->getEntityService()->persist($order);
        }
    }
}
