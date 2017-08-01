<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class RemoveProduct
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class RemoveProduct
{
    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    protected $cartSessionService;

    protected $router;

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
     * @param CoreEvent $event
     */
    public function onCartRemoveProduct(CoreEvent $event)
    {
        $returnData = $event->getReturnData();

        $recollectShipping = [];
        $success = 0;
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');

        $productId = $event->getProductId()
            ? $event->getProductId()
            : $request->get('product_id', '');

        $cartSession = $this->getCartSessionService();
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

        $cartItem = $cartSession->getCart()->findItem('product_id', $productId);

        if ($cartItem) {

            $customerAddressId = $cartItem->get('customer_address_id', 'main');
            $srcAddressKey = $cartItem->get('source_address_key', 'main');

            $recollectShipping[] = new ArrayWrapper([
                'customer_address_id' => $customerAddressId,
                'source_address_key' => $srcAddressKey
            ]);

            $event->setRecollectShipping($recollectShipping);

            $cartItemEntities = $cartEntity->getCartItems();
            if ($cartItemEntities) {
                foreach($cartItemEntities as $cartItemEntity) {
                    if ($cartItemEntity->getProductId() == $productId) {
                        $this->getEntityService()->remove($cartItemEntity);
                        break;
                    }
                }
            }

            $this->getCartSessionService()->removeProductId($productId);
            $cartItems = $cartSession->getCart()->getItems();
            // check if items still need a shipment for this address
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
            }

            $success = 1;
            $this->getCartSessionService()->collectTotals();

        } else {

            // todo: display error message

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
