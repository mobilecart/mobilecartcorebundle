<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use MobileCart\CoreBundle\Constants\EntityConstants;

class AddProduct
{
    protected $entityService;

    protected $cartSessionService;

    protected $shippingService;

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

    public function onCartAddProduct(Event $event)
    {
        $this->setEvent($event);
        $returnData = $this->getReturnData();
        $success = 0;
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');

        // todo : check inventory
        $product = null;
        $productId = $request->get('id', '');
        $qty = $request->get('qty', 1);
        $simpleProductId = $request->get('simple_id', '');
        if (!$productId) {
            $productId = $event->get('product_id');
            $qty = $event->get('qty');
        }

        $idField = $request->get('key', 'id');
        $idFields = ['id', 'sku'];
        if (!in_array($idField, $idFields)) {
            $idField = 'id';
        }

        $cart = $this->getCartSessionService()
            ->initCart()
            ->getCart();

        if ($idField != 'id') {
            if ($item = $cart->findItem($idField, $productId)) {
                $productId = $item->getProductId();
            } else {
                $product = $this->getEntityService()->findOneBy(EntityConstants::PRODUCT, [
                    $idField => $productId,
                ]);

                if ($product) {
                    $productId = $product->getId();
                }
            }
        }

        $event->setProductId($productId);
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

        $event->setProductId($productId)
            ->setSimpleProductId($simpleProductId)
            ->setQty($qty);

        if ($this->getCartSessionService()->hasProductId($productId)) {

            if ($event->getIsAdd()) {

                // todo: check inventory

                $this->getCartSessionService()
                    ->addProductQty($productId, $qty);

            } else {

                $this->getCartSessionService()
                    ->setProductQty($productId, $qty);

            }

            // update db
            if ($cartItem = $cart->findItem('product_id', $productId)) {
                if ($cartItem->getId()) {
                    // update row

                    $cartItemEntity = $this->getEntityService()
                        ->find(EntityConstants::CART_ITEM, $cartItem->getId());

                    $cartItemEntity->setQty($cartItem->getQty());

                    $this->getEntityService()->persist($cartItemEntity);

                } else {
                    // insert row

                    $cartItemEntity = $this->getEntityService()
                        ->getInstance(EntityConstants::CART_ITEM);

                    $cartItemEntity->setCart($cartEntity)
                        ->setSku($cartItem->getSku())
                        ->setQty($cartItem->getQty())
                        ->setJson($cartItem->toJson());

                    $this->getEntityService()->persist($cartItemEntity);

                    $cartItem->setId($cartItemEntity->getId());
                }
            }

            $success = 1;

        } else if ($this->getCartSessionService()->hasProductId($simpleProductId)) {

            if ($event->getIsAdd()) {

                $this->getCartSessionService()
                    ->addProductQty($simpleProductId, $qty);

            } else {

                $this->getCartSessionService()
                    ->setProductQty($simpleProductId, $qty);
            }

            // update db
            if ($cartItem = $cart->findItem('product_id', $simpleProductId)) {
                if ($cartItem->getId()) {
                    // update row

                    $cartItemEntity = $this->getEntityService()
                        ->find(EntityConstants::CART_ITEM, $cartItem->getId());

                    $cartItemEntity->setQty($cartItem->getQty());

                    $this->getEntityService()->persist($cartItemEntity);

                } else {
                    // insert row

                    $cartItemEntity = $this->getEntityService()
                        ->getInstance(EntityConstants::CART_ITEM);

                    $cartItemEntity->setCart($cartEntity)
                        ->setSku($cartItem->getSku())
                        ->setQty($cartItem->getQty())
                        ->setJson($cartItem->toJson());

                    $this->getEntityService()->persist($cartItemEntity);

                    $cartItem->setId($cartItemEntity->getId());
                }
            }

            $success = 1;

        } else if ($productId) {

            if ($idField == 'id' && !$product) {
                $product = $this->getEntityService()->find(EntityConstants::PRODUCT, $productId);
            }

            if (!$product) {
                throw new NotFoundHttpException("Product not found with ID: '{$simpleProductId}''");
            }

            $parentOptions = [];
            if ($simpleProductId) {

                $child = $this->getEntityService()->find(EntityConstants::PRODUCT, $simpleProductId);
                if (!$child) {
                    throw new NotFoundHttpException("Child Product not found with ID: '{$simpleProductId}''");
                }

                $parentOptions['id'] = $product->getId();
                $parentOptions['sku'] = $product->getSku();
                $parentOptions['slug'] = $product->getSlug();

                $this->getCartSessionService()
                    ->addProduct($child, $qty, $parentOptions);

                $cartItem = $this->getCartSessionService()
                    ->getCart()
                    ->findItem('product_id', $child->getId());

                $itemJson = $cartItem
                    ? $cartItem->toJson()
                    : json_encode($child->getData());

                // insert row
                $cartItemEntity = $this->getEntityService()
                    ->getInstance(EntityConstants::CART_ITEM);

                $cartItemEntity->setCart($cartEntity)
                    ->setSku($child->getSku())
                    ->setQty($qty)
                    ->setJson($itemJson);

                $this->getEntityService()->persist($cartItemEntity);

                $cart->findItem('sku', $child->getSku())
                    ->setId($cartItemEntity->getId());

                $success = 1;

            } else {

                $this->getCartSessionService()
                    ->addProduct($product, $qty, $parentOptions);

                // insert row
                $cartItemEntity = $this->getEntityService()
                    ->getInstance(EntityConstants::CART_ITEM);

                $cartItem = $this->getCartSessionService()
                    ->getCart()
                    ->findItem('product_id', $product->getId());

                $itemJson = $cartItem
                    ? $cartItem->toJson()
                    : json_encode($product->getData());

                $cartItemEntity->setCart($cartEntity)
                    ->setSku($product->getSku())
                    ->setQty($qty)
                    ->setJson($itemJson);

                $this->getEntityService()->persist($cartItemEntity);

                $cart->findItem('sku', $product->getSku())
                    ->setId($cartItemEntity->getId());

                $success = 1;
            }
        }

        $cart = $this->getCartSessionService()
            ->collectShippingMethods()
            ->collectTotals()
            ->getCart();

        // update db
        $cartEntity->setJson($cart->toJson())
            ->setCreatedAt(new \DateTime('now'));

        if ($customerId && !$cartEntity->getCustomer()) {

            if (!$customerEntity) {

                $customerEntity = $this->getEntityService()
                    ->find(EntityConstants::CUSTOMER, $customerId);

            }

            $cartEntity->setCustomer($customerEntity);
        }

        $this->getEntityService()->persist($cartEntity);
        $event->setCartEntity($cartEntity);
        $cartId = $cartEntity->getId();
        $cart->setId($cartId);

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

        $event->setReturnData($returnData);
        $event->setResponse($response);
    }
}
