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

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
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
        $recollectShipping = [];
        $success = false;
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');

        $productId = $event->getProductId()
            ? $event->getProductId()
            : $request->get('product_id', '');

        $cartSession = $this->getCartSessionService();
        $cart = $cartSession->getCart();
        $cartId = $cart->getId();
        $customerId = $cart->getCustomer()->getId();

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

            // remove all shipments and methods if the cart is empty
            if (!$cartSession->getCart()->getItems()) {
                $this->getCartSessionService()->removeShipments();
                $this->getCartSessionService()->removeShippingMethods();
            }

            $success = true;
            $this->getCartSessionService()->collectTotals();

        } else {
            $event->addErrorMessage("Specified item is not in your cart");
        }

        $event->setReturnData('cart', $cart);
        $event->setReturnData('success', $success);

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
