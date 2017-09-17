<?php

namespace MobileCart\CoreBundle\EventListener\OrderPayment;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\CartComponent\Payment;

/**
 * Class OrderPaymentInsert
 * @package MobileCart\CoreBundle\EventListener\OrderPayment
 */
class OrderPaymentInsert
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

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
    public function onOrderPaymentInsert(CoreEvent $event)
    {
        /** @var \MobileCart\CoreBundle\Entity\OrderPayment $entity */
        $entity = $event->getEntity();
        $order = $entity->getOrder();
        $baseCurrency = $this->getCartService()->getCartTotalService()->getCurrencyService()->getBaseCurrency();
        $currency = $order->getCurrency();
        $entity->setBaseCurrency($baseCurrency);

        if ($baseCurrency == $currency) {

            $entity->setCurrency($entity->getBaseCurrency())
                ->setAmount($entity->getBaseAmount());

        } else {
            // todo:

        }

        $entity->setCreatedAt(new \DateTime('now'));

        $this->getEntityService()->persist($entity);
        $event->addSuccessMessage('Payment Created!');


    }
}
