<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class UpdateTotalsShipping
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
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

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

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
     * @param $entityService
     * @return $this
     */
    public function setEntityService($entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\DoctrineEntityService
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param $cartSessionService
     * @return $this
     */
    public function setCartSessionService($cartSessionService)
    {
        $this->cartSessionService = $cartSessionService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartSessionService
     */
    public function getCartSessionService()
    {
        return $this->cartSessionService;
    }

    /**
     * @param $logger
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param CoreEvent $event
     */
    public function onUpdateTotalsShipping(CoreEvent $event)
    {
        if (!$event->getReturnData('success')) {
            return;
        }

        $currencyService = $this->getCartSessionService()->getCurrencyService();
        $cartItems = $this->getCartSessionService()->getCart()->getItems();

        $customerId = ($this->getCartSessionService()->getCart()->getCustomer() && $this->getCartSessionService()->getCart()->getCustomer()->getId())
            ? $this->getCartSessionService()->getCart()->getCustomer()->getId()
            : 0;

        // re-collect shipping methods , if necessary
        $recollectAddresses = $event->getRecollectShipping();
        if ($recollectAddresses) {
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
                    $this->getCartSessionService()->removeShipments();
                    $this->getCartSessionService()->removeShippingMethods();
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

        $postcodes = [];
        if ($customerId) {
            $addresses = $this->getCartSessionService()->getCart()->getCustomer()->getAddresses();
            if ($addresses) {
                foreach($addresses as $address) {
                    $postcodes[] = $address['postcode'];
                }
            }
        }
        $this->getLogger()->info("UpdateTotalsShipment : Cart Customer ID: {$customerId} , postcodes: " . implode(', ', $postcodes));

        $baseCurrency = $currencyService->getBaseCurrency();

        $currency = strlen($cart->getCurrency())
            ? $cart->getCurrency()
            : $baseCurrency;

        $cartEntity = $event->get('cart_entity')
            ? $event->get('cart_entity')
            : $this->getEntityService()->find(EntityConstants::CART, $cart->getId());

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
