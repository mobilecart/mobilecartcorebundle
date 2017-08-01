<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\CartComponent\Shipment;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AddShipment
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class AddShipment
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    public $cartSessionService;

    /**
     * @var \MobileCart\CoreBundle\Service\ShippingService
     */
    public $shippingService;

    protected $router;

    public function setRouter($router)
    {
        $this->router = $router;
        return $this;
    }

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
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
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
     * @param $shippingService
     * @return $this
     */
    public function setShippingService($shippingService)
    {
        $this->shippingService = $shippingService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ShippingService
     */
    public function getShippingService()
    {
        return $this->shippingService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCartAddShipment(CoreEvent $event)
    {
        $returnData = $event->getReturnData();

        $shippingService = $this->getShippingService();
        $cartSession = $this->getCartSessionService(); //->initCart()->collectShippingMethods();

        /** @var \MobileCart\CoreBundle\CartComponent\Cart $cart */
        $cart = $cartSession->getCart();
        $cartId = $cart->getId();

        $customerId = $cart->getCustomer()->getId();
        $customerEntity = false;
        $cartItems = $cart->getItems();

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

        if ($shippingService->getIsMultiShippingEnabled()) {

            $codes = $request->get('shipping_methods', []); // r[source_address_key][customer_address_id] = $code
            if (is_array($codes) && count($codes)) {

                foreach($codes as $srcAddressKey => $customerAddressIds) {

                    if (!$customerAddressIds) {
                        continue;
                    }

                    foreach($customerAddressIds as $anAddressId => $methodCode) {

                        if ($anAddressId != 'main' && !is_numeric($anAddressId)) {
                            $anAddressId = (int) str_replace('address_', '', $anAddressId);
                        }

                        if ($rate = $cart->findShippingMethod('code', $methodCode, $anAddressId, $srcAddressKey)) {

                            $productIds = [];
                            if ($cartItems) {
                                foreach($cartItems as $item) {
                                    if ($item->get('customer_address_id', 'main') == $anAddressId
                                        && $item->get('source_address_key', 'main') == $srcAddressKey
                                    ) {
                                        $productIds[] = $item->getProductId();
                                    }
                                }
                            }

                            $shipment = new Shipment();
                            $shipment->fromArray($rate->getData());

                            $cartSession
                                ->removeShipments($anAddressId, $srcAddressKey)
                                ->addShipment($shipment, $anAddressId, $productIds, $srcAddressKey)
                                ->collectTotals();

                            $success = 1;
                        }
                    }

                    if ($success) {
                        $cartSession->collectTotals();
                    }
                }
            }

        } else {

            $code = $request->get('shipping_method', ''); // single shipping method
            if ($cart->hasShippingMethodCode($code)) {

                $rate = $cart->getShippingMethod($cart->findShippingMethodIdx('code', $code));
                $shipment = new Shipment();
                $shipment->fromArray($rate->getData());
                $productIds = $cart->getProductIds();

                $cartSession
                    ->removeShipments('main')
                    ->addShipment($shipment, 'main', $productIds)
                    ->collectTotals();

                $success = 1;
            }

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
