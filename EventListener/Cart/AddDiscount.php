<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\CartComponent\Discount as CartDiscount;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AddDiscount
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
     * @param Event $event
     */
    public function onCartAddDiscount(Event $event)
    {
        $this->setEvent($event);
        $returnData = $event->getReturnData();

        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $code = $request->get('code', '');

        $cartSession = $this->getCartSessionService();
        $cart = $cartSession->getCart();
        $cartId = $cart->getId();

        $cartEntity = $cartId
            ? $this->getEntityService()->find(EntityConstants::CART, $cartId)
            : $this->getEntityService()->getInstance(EntityConstants::CART);

        $event->setCartEntity($cartEntity);

        $discountEntity = $this->getEntityService()->findOneBy(EntityConstants::DISCOUNT, [
            'coupon_code' => $code,
        ]);

        $isValid = false;
        if ($discountEntity) {
            $discount = new CartDiscount();
            $discount->fromArray($discountEntity->getData());

            $isValid = $discount->reapplyIfValid($cart);

            if ($isValid && $discount->hasPromoSkus()) {
                foreach($discount->getPromoSkus() as $sku => $qty) {

                    if ($cart->hasSku($sku)) {
                        continue;
                    }

                    $product = $this->getEntityService()
                        ->findOneBy(EntityConstants::PRODUCT, [
                            'sku' => $sku,
                        ]);

                    if ($product) {

                        $item = $cart->createItem();
                        $data = $product->getData();
                        $data['product_id'] = $data['id'];
                        unset($data['id']);
                        $item->fromArray($data);
                        $item->setQty($qty);
                        $cart->addItem($item);
                        $event->setProductId($product->getId());

                    } else {

                        switch($discount->get('to')) {
                            case CartDiscount::$toItems:
                                if (
                                    !$cart->hasItems()
                                    && !$discount->hasPromoSkus()
                                ) {
                                    $cart->removeDiscount($discount);
                                }
                                break;
                            case CartDiscount::$toShipments:
                                if (!$cart->hasShipments()) {
                                    $cart->removeDiscount($discount);
                                }
                                break;
                            case CartDiscount::$toSpecified:
                                if (!$cart->hasItems() && !$cart->hasShipments()) {
                                    $cart->removeDiscount($discount);
                                }
                                break;
                            default:

                                break;
                        }

                    }
                }
            }
        }

        if ($isValid) {

            $cart = $this->getCartSessionService()
                ->setCart($cart)
                //->collectShippingMethods() // shouldnt have to re-collect shipping methods
                ->collectTotals()
                ->getCart();
        }

        $returnData['cart'] = $cart;
        $returnData['is_valid_code'] = $isValid;
        $returnData['success'] = $isValid;

        if ($isValid && $request->getSession()) {
            $request->getSession()->getFlashBag()->add(
                'success',
                'Discount Successfully Added!'
            );
        }

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
