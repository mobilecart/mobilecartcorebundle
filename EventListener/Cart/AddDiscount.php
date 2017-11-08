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
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    public $cartService;

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
     * @param $cartService
     * @return $this
     */
    public function setCartService($cartService)
    {
        $this->cartService = $cartService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartService
     */
    public function getCartService()
    {
        return $this->cartService;
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
        $event->set('format', $format);
        $code = $request->get('code', '');

        $discountEntity = $this->getEntityService()->findOneBy(EntityConstants::DISCOUNT, [
            'coupon_code' => $code,
        ]);

        $isValid = false;
        if ($discountEntity) {
            $discount = new CartDiscount();
            $discount->fromArray($discountEntity->getData());

            $isValid = $discount->reapplyIfValid($cart);
            if ($isValid && $discount->hasPromoSkus()) {
                foreach($discount->getPromoSkus() as $sku) {

                    if ($this->getCartService()->hasSku($sku)) {
                        continue;
                    }

                    $product = $this->getEntityService()
                        ->findOneBy(EntityConstants::PRODUCT, [
                            'sku' => $sku,
                        ]);

                    if ($product) {

                        $item = $this->getCartService()->convertProductToItem($product);

                        $item->setPromoQty(1)
                            ->setPrice(0.00)
                            ->setBasePrice(0.00);

                        $this->getCartService()->addItem($item);
                        $event->setProductId($product->getId());

                    } else {

                        switch($discount->getAppliedTo()) {
                            case CartDiscount::APPLIED_TO_ITEMS:
                                if (
                                    !$this->getCartService()->hasItems()
                                    && !$discount->hasPromoSkus()
                                ) {
                                    $this->getCartService()->removeDiscount($discount);
                                }
                                break;
                            case CartDiscount::APPLIED_TO_SHIPMENTS:
                                if (!$this->getCartService()->hasShipments()) {
                                    $this->getCartService()->removeDiscount($discount);
                                }
                                break;
                            case CartDiscount::APPLIED_TO_SPECIFIED:
                                if (!$this->getCartService()->hasItems() && !$this->getCartService()->hasShipments()) {
                                    $this->getCartService()->removeDiscount($discount);
                                }
                                break;
                            default:

                                break;
                        }

                    }
                }
            }
        }

        $event->setReturnData('is_valid_code', $isValid);
        $event->setReturnData('success', $isValid);

        if ($isValid) {
            $event->addSuccessMessage('Discount Successfully Added!');
        }
    }
}
