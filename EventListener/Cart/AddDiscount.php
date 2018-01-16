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
class AddDiscount extends BaseCartListener
{
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

        // parse/convert API requests
        switch($event->getContentType()) {
            case CoreEvent::JSON:

                $apiRequest = $event->getApiRequest()
                    ? $event->getApiRequest()
                    : @ (array) json_decode($event->getRequest()->getContent());

                if (isset($apiRequest['code'])) {
                    $event->getRequest()->request->set('code', $apiRequest['code']);
                }

                break;
            default:

                break;
        }

        // continue base logic
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

        $event->setReturnData('is_valid_code', $isValid);
        $event->setSuccess($isValid);

        if ($isValid) {
            $event->addSuccessMessage('Discount Successfully Added!');
        }
    }
}
