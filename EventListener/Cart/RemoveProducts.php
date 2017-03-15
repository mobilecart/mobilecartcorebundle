<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use MobileCart\CoreBundle\Constants\EntityConstants;

class RemoveProducts
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    protected $cartSessionService;

    /**
     * @var \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    protected $entityService;

    protected $router;

    /**
     * @var Event
     */
    protected $event;

    /**
     * @param $event
     * @return $this
     */
    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    protected function getEvent()
    {
        return $this->event;
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
     * @param Event $event
     */
    public function onCartRemoveProducts(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $cartSession = $this->getCartSessionService();
        $cart = $cartSession->getCart();
        $cartId = $cart->getId();

        $customerId = $cart->getCustomer()->getId();
        $customerEntity = null;

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

        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');

        $cartItemEntities = $cartEntity->getCartItems();
        if ($cartItemEntities) {
            foreach($cartItemEntities as $cartItemEntity) {
                $this->getEntityService()->remove($cartItemEntity);
            }
        }

        $this->getCartSessionService()->removeItems();
        $this->getCartSessionService()->removeShipments('');
        $this->getCartSessionService()->removeShippingMethods('');

        $cart = $this->getCartSessionService()
            ->collectTotals()
            ->getCart();

        $returnData['cart'] = $cart;
        $returnData['success'] = 1;

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

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }
}
