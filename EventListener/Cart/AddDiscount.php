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
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @param \MobileCart\CoreBundle\Service\CartService $cartService
     * @return $this
     */
    public function setCartService(\MobileCart\CoreBundle\Service\CartService $cartService)
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
     * @return \MobileCart\CoreBundle\Service\ShippingService
     */
    public function getShippingService()
    {
        return $this->getCartService()->getShippingService();
    }

    /**
     * @param CoreEvent $event
     */
    public function onCartAddDiscount(CoreEvent $event)
    {
        $isValid = false;

        // parse/convert API requests
        switch($event->getContentType()) {
            case CoreEvent::JSON:

                $apiRequest = $event->getApiRequest()
                    ? $event->getApiRequest()
                    : @ (array) json_decode($event->getRequest()->getContent());

                $code = isset($apiRequest['code'])
                    ? $apiRequest['code']
                    : '';

                break;
            default:

                $code = $event->getRequest()->get('code', '');

                break;
        }

        $discountEntity = strlen($code)
            ? $this->getEntityService()->findOneBy(EntityConstants::DISCOUNT, ['coupon_code' => $code])
            : null;

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
                        $event->set('product_id', $product->getId());

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

        $event->setSuccess($isValid);
        if ($isValid) {
            $event->addSuccessMessage('Discount Successfully Added!');
        }
    }
}
