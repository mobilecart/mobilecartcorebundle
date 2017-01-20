<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\EventDispatcher\Event;

class UpdateTotalsShipping
{

    /**
     * @var \MobileCart\CoreBundle\Service\DoctrineEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    protected $cartSessionService;

    protected $router;

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

    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    public function getEntityService()
    {
        return $this->entityService;
    }

    public function setCartSessionService($cartSessionService)
    {
        $this->cartSessionService = $cartSessionService;
        return $this;
    }

    public function getCartSessionService()
    {
        return $this->cartSessionService;
    }

    public function onUpdateTotalsShipping(Event $event)
    {
        $currencyService = $this->getCartSessionService()->getCurrencyService();

        // collect shipping methods and totals
        $cart = $this->getCartSessionService()
            ->collectShippingMethods('main')
            ->collectTotals()
            ->getCart();

        $baseCurrency = $currencyService->getBaseCurrency();

        $currency = strlen($cart->getCurrency())
            ? $cart->getCurrency()
            : $baseCurrency;

        $cartEntity = $this->getEntityService()->find(EntityConstants::CART, $cart->getId());
        $customerId = $cart->getCustomer()->getId();
        $customerEntity = $this->getEntityService()->find(EntityConstants::CUSTOMER, $customerId);

        if ($customerEntity) {
            $cartEntity->setCustomer($customerEntity);
        }

        // update cart row in db
        $cartEntity->setJson($cart->toJson())
            ->setCreatedAt(new \DateTime('now'))
            ->setCurrency($currency)
            ->setBaseCurrency($baseCurrency);

        // set totals on cart entity
        $totals = $cart->getTotals();
        foreach($totals as $total) {
            switch($total->getKey()) {
                case 'items':
                    $cartEntity->setBaseItemTotal($total->getValue());
                    if ($baseCurrency == $currency) {
                        $cartEntity->setItemTotal($total->getValue());
                    } else {
                        $cartEntity->setItemTotal($currencyService->convert($total->getValue(), $currency));
                    }
                    break;
                case 'shipments':
                    $cartEntity->setBaseShippingTotal($total->getValue());
                    if ($baseCurrency == $currency) {
                        $cartEntity->setShippingTotal($total->getValue());
                    } else {
                        $cartEntity->setShippingTotal($currencyService->convert($total->getValue(), $currency));
                    }
                    break;
                case 'tax':
                    $cartEntity->setBaseTaxTotal($total->getValue());
                    if ($baseCurrency == $currency) {
                        $cartEntity->setTaxTotal($total->getValue());
                    } else {
                        $cartEntity->setTaxTotal($currencyService->convert($total->getValue(), $currency));
                    }
                    break;
                case 'discounts':
                    $cartEntity->setBaseDiscountTotal($total->getValue());
                    if ($baseCurrency == $currency) {
                        $cartEntity->setDiscountTotal($total->getValue());
                    } else {
                        $cartEntity->setDiscountTotal($currencyService->convert($total->getValue(), $currency));
                    }
                    break;
                case 'grand_total':
                    $cartEntity->setBaseTotal($total->getValue());
                    if ($baseCurrency == $currency) {
                        $cartEntity->setTotal($total->getValue());
                    } else {
                        $cartEntity->setTotal($currencyService->convert($total->getValue(), $currency));
                    }
                    break;
                default:
                    // no-op
                    break;
            }
        }

        $cartEntity->setJson($cart->toJson());

        // update Cart in database
        $this->getEntityService()->persist($cartEntity);

        $event->setCartEntity($cartEntity);

        $cartId = $cartEntity->getId();
        $cart->setId($cartId);

    }

}
