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

    public function onCartAddDiscount(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $code = $request->get('code', '');

        $cartSession = $this->getCartSessionService();
        $cart = $cartSession->getCart();
        $cartId = $cart->getId();

        $cartEntity = $cartId
            ? $this->getEntityService()->find(EntityConstants::CART, $cartId)
            : $this->getEntityService()->getInstance(EntityConstants::CART);

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
