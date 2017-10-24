<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\CartComponent\Discount as CartDiscount;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class AddDiscount
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
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

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

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
    public function onCartAddDiscount(CoreEvent $event)
    {
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
                        //$item->setPrice('0.00'); // dont need to assume zero price for coupon promos, like we do in DiscountTotal
                        $cart->addItem($item);
                        $event->setProductId($product->getId());

                    } else {

                        switch($discount->getAppliedTo()) {
                            case CartDiscount::APPLIED_TO_ITEMS:
                                if (
                                    !$cart->hasItems()
                                    && !$discount->hasPromoSkus()
                                ) {
                                    $cart->removeDiscount($discount);
                                }
                                break;
                            case CartDiscount::APPLIED_TO_SHIPMENTS:
                                if (!$cart->hasShipments()) {
                                    $cart->removeDiscount($discount);
                                }
                                break;
                            case CartDiscount::APPLIED_TO_SPECIFIED:
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

        $event->setReturnData('cart', $cart);
        $event->setReturnData('is_valid_code', $isValid);
        $event->setReturnData('success', $isValid);

        if ($isValid) {
            $event->addSuccessMessage('Discount Successfully Added!');
        }

        switch($format) {
            case 'json':
                $event->setResponse(new JsonResponse($event->getReturnData()));
                break;
            default:
                $event->setResponse(new RedirectResponse($this->getRouter()->generate('cart_view', [])));
                break;
        }
    }
}
