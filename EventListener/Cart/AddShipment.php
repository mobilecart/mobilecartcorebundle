<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AddShipment
{
    public $entityService;

    public $cartSessionService;

    public $shippingService;

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

    public function setShippingService($shippingService)
    {
        $this->shippingService = $shippingService;
        return $this;
    }

    public function getShippingService()
    {
        return $this->shippingService;
    }

    public function onCartAddShipment(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $cartSession = $this->getCartSessionService(); //->initCart()->collectShippingMethods();
        $cart = $cartSession->getCart();
        $cartId = $cart->getId();

        $customerId = $cart->getCustomer()->getId();
        $customerEntity = false;

        $cartEntity = $cartId
            ? $this->getEntityService()->find(EntityConstants::CART, $cartId)
            : $this->getEntityService()->getInstance(EntityConstants::CART);

        if (!$cartId) {

            $cartEntity->setJson($cart->toJson())
                ->setCreatedAt(new \DateTime('now'));

            if ($customerId) {

                $customerEntity = $this->getEntityService()
                    ->find(EntityConstants::CUSTOMER, $customerId);

                if ($customerEntity) {
                    $cartEntity->setCustomer($customerEntity);
                }
            }

            $this->getEntityService()->persist($cartEntity);
            $cartId = $cartEntity->getId();
            $cart->setId($cartId);
        }

        $success = 0;
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');

        $code = $request->get('shipping_method', ''); // single shipping method
        $codes = $request->get('shipping_methods', []); // array of shipping methods

        $recollect = $request->get('recollect', 0);
        $addressId = $request->get('address_id', 'main');

        if ($recollect) {
            $cartSession->collectShippingMethods($addressId);
            $cart = $cartSession->getCart();
        }

        if (is_array($codes) && count($codes)) {
            $cartItems = $cart->getItems();
            foreach($codes as $anAddressId => $methodCode) {

                if ($anAddressId != 'main' && !is_numeric($anAddressId)) {
                    $anAddressId = (int) str_replace('address_', '', $anAddressId);
                }

                if ($cart->hasShippingMethodCode($methodCode, $anAddressId)) {

                    $productIds = [];
                    if ($cartItems) {
                        foreach($cartItems as $item) {
                            if ($item->get('customer_address_id') == $anAddressId) {
                                $productIds[] = $item->getProductId();
                            }
                        }
                    }

                    $shipment = $cart->getShippingMethod($cart->findShippingMethodIdx('code', $methodCode, $anAddressId), $anAddressId);
                    $cartSession
                        ->removeShipments($anAddressId)
                        ->addShipment($shipment, $anAddressId, $productIds);

                    $success = 1;
                }
            }

            if ($success) {
                $cartSession->collectTotals();
            }

        } elseif ($cart->hasShippingMethodCode($code, $addressId)) {

            $shipment = $cart->getShippingMethod($cart->findShippingMethodIdx('code', $code, $addressId));
            $productIds = $cart->getProductIds();

            $cartSession
                ->removeShipments($addressId)
                ->addShipment($shipment, $addressId, $productIds)
                ->collectTotals();

            $success = 1;
        }

        if ($success) {

            $cart = $cartSession->getCart();

            // update db
            $cartEntity->setJson($cart->toJson());

            $currencyService = $this->getCartSessionService()->getCurrencyService();
            $baseCurrency = $currencyService->getBaseCurrency();

            $currency = strlen($cart->getCurrency())
                ? $cart->getCurrency()
                : $baseCurrency;

            // set totals
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

            // update Cart in database
            $this->getEntityService()->persist($cartEntity);
            $event->setCartEntity($cartEntity);
        }

        $returnData['cart'] = $cart;
        $returnData['success'] = $success;

        $response = '';
        switch($format) {
            case 'json':
                $response = new JsonResponse($returnData);
                break;
            default:
                $params = [];
                $route = 'cart_view';
                $url = $this->getRouter()->generate($route, $params);
                $response = new RedirectResponse($url);
                break;
        }

        $event->setReturnData($returnData)
            ->setResponse($response);
    }
}
