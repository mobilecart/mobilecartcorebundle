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

        $cartItems = $this->getCartSessionService()->getCart()->getItems();

        // re-collect shipping methods , if necessary
        if ($recollectAddresses = $event->getRecollectShipping()) {
            foreach($recollectAddresses as $recollectAddress) {

                $customerAddressId = $recollectAddress->get('customer_address_id', 'main');
                $srcAddressKey = $recollectAddress->get('source_address_key', 'main');
                $hasItems = false;
                if ($cartItems) {
                    foreach($cartItems as $cartItem) {
                        if ($cartItem->get('customer_address_id', 'main') == $customerAddressId
                            && $cartItem->get('source_address_key', 'main') == $srcAddressKey
                        ) {
                            $hasItems = true;
                        }
                    }
                }

                // remove shipments and shipping methods
                if (!$hasItems) {
                    $this->getCartSessionService()->removeShipments($customerAddressId, $srcAddressKey);
                    $this->getCartSessionService()->removeShippingMethods($customerAddressId, $srcAddressKey);
                    continue;
                }

                // shipment quotes are stored in the cart json . they don't have their own table, so we dont need to persist
                $this->getCartSessionService()
                    ->collectShippingMethods($customerAddressId, $srcAddressKey);
            }
        }

        // collect totals
        $cart = $this->getCartSessionService()
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

        // todo : update tax, discount values in cart items

        // update Cart in database
        $this->getEntityService()->persist($cartEntity);

        $event->setCartEntity($cartEntity);

        $cartId = $cartEntity->getId();
        $cart->setId($cartId);

    }

}
