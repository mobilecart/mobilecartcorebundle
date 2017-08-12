<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Event\CoreEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class AddProduct
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class AddProduct
{
    /**
     * @var \MobileCart\CoreBundle\Service\DoctrineEntityService
     */
    protected $entityService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartSessionService
     */
    protected $cartSessionService;

    /**
     * @var int|bool
     */
    protected $redirectToCart = 1;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \MobileCart\CoreBundle\Entity\Cart
     */
    protected $cartEntity;

    /**
     * @var \MobileCart\CoreBundle\Entity\Customer
     */
    protected $customerEntity;

    /**
     * @var \MobileCart\CoreBundle\CartComponent\Item
     */
    protected $cartItem;

    /**
     * @var \MobileCart\CoreBundle\Entity\CartItem
     */
    protected $cartItemEntity;

    /**
     * @var \MobileCart\CoreBundle\Entity\Product
     */
    protected $product;

    /**
     * @var int|null
     */
    protected $productId;

    /**
     * @var bool
     */
    protected $isAdd = true;

    /**
     * @var bool
     */
    protected $hasTierPriceChange = false;

    /**
     * @var bool
     */
    protected $enableQtyCheck = true;

    /**
     * @var int
     */
    protected $qty = 1;

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @var bool
     */
    protected $success = false;

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
     * @return \MobileCart\CoreBundle\Service\DoctrineEntityService
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
     * @param $yesNo
     * @return $this
     */
    public function setRedirectToCart($yesNo)
    {
        $this->redirectToCart = $yesNo;
        return $this;
    }

    /**
     * @return bool|int
     */
    public function getRedirectToCart()
    {
        return $this->redirectToCart;
    }

    /**
     * @return $this
     */
    public function initCartEntity()
    {
        $cart = $this->getCartSessionService()->getCart();
        $cartId = $cart->getId();
        $customerId = $cart->getCustomer()->getId();

        $cartEntity = $this->getEntityService()->getInstance(EntityConstants::CART);
        if ($cartId) {
            $cartEntity = $this->getEntityService()->find(EntityConstants::CART, $cartId);
            if (!$cartEntity) {
                $cartEntity = $this->getEntityService()->getInstance(EntityConstants::CART);
                $cartEntity->setJson($cart->toJson())
                    ->setCreatedAt(new \DateTime('now'));
            }
        } else {
            $cartEntity->setJson($cart->toJson())
                ->setCreatedAt(new \DateTime('now'));
        }

        if ($customerId) {
            $customerEntity = $this->getEntityService()->find(EntityConstants::CUSTOMER, $customerId);
            if ($customerEntity) {
                $cartEntity->setCustomer($customerEntity);
            }
        }

        // always save the cart
        $this->getEntityService()->persist($cartEntity);
        $cartId = $cartEntity->getId();
        $cart->setId($cartId);

        $this->setCartEntity($cartEntity);
        return $this;
    }

    /**
     * @param $cartEntity
     * @return $this
     */
    public function setCartEntity($cartEntity)
    {
        $this->cartEntity = $cartEntity;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Entity\Cart
     */
    public function getCartEntity()
    {
        return $this->cartEntity;
    }

    /**
     * @param $customerEntity
     * @return $this
     */
    public function setCustomerEntity($customerEntity)
    {
        $this->customerEntity = $customerEntity;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Entity\Customer
     */
    public function getCustomerEntity()
    {
        return $this->customerEntity;
    }

    /**
     * @param $value
     * @param string $idField
     * @return mixed
     */
    public function loadProduct($value, $idField = 'id')
    {
        if ($idField == 'product_id') {
            $idField = 'id';
        }

        if ($idField == 'id') {
            return $this->getEntityService()->find(EntityConstants::PRODUCT, $value);
        }

        return $this->getEntityService()->findOneBy(EntityConstants::PRODUCT, [
            $idField => $value,
        ]);
    }

    /**
     * @param $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param $qty
     * @return $this
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
        return $this;
    }

    /**
     * @return int
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param $isAdd
     * @return $this
     */
    public function setIsAdd($isAdd)
    {
        $this->isAdd = $isAdd;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAdd()
    {
        return $this->isAdd;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setHasTierPriceChange($yesNo)
    {
        $this->hasTierPriceChange = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHasTierPriceChange()
    {
        return $this->hasTierPriceChange;
    }

    /**
     * @param array $errors
     * @return $this
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param $cartItem
     * @return $this
     */
    public function setCartItem($cartItem)
    {
        $this->cartItem = $cartItem;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\CartComponent\Item
     */
    public function getCartItem()
    {
        return $this->cartItem;
    }

    /**
     * @param $cartItemEntity
     * @return $this
     */
    public function setCartItemEntity($cartItemEntity)
    {
        $this->cartItemEntity = $cartItemEntity;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Entity\CartItem
     */
    public function getCartItemEntity()
    {
        return $this->cartItemEntity;
    }

    /**
     * @param $success
     * @return $this
     */
    public function setSuccess($success)
    {
        $this->success = $success;
        return $this;
    }

    /**
     * @return int
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setEnableQtyCheck($yesNo)
    {
        $this->enableQtyCheck = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnableQtyCheck()
    {
        return $this->enableQtyCheck;
    }

    /**
     * @param $cartItem
     * @param CoreEvent $event
     * @return bool
     */
    public function meetsCriteria($cartItem, CoreEvent &$event)
    {
        $minQty = (int) $cartItem->getMinQty();
        $availQty = $cartItem->getAvailQty();
        $isQtyManaged = $cartItem->getIsQtyManaged();

        $newQty = $this->getIsAdd()
            ? $cartItem->getQty() + $this->getQty()
            : $this->getQty();

        $minQtyMet = $minQty == 0 || ($minQty > 0 && $newQty >= $minQty);
        $maxQtyMet = !$isQtyManaged || ($isQtyManaged && $newQty < $availQty);

        if (!$cartItem->getIsEnabled()) {
            $event->addErrorMessage("Product is not enabled : {$cartItem->getSku()}");
            return false;
        }

        if (!$cartItem->getIsInStock()) {
            $event->addErrorMessage("Product is not in stock : {$cartItem->getSku()}");
            return false;
        }

        if ($this->getEnableQtyCheck()) {

            if (!$minQtyMet) {
                $event->addErrorMessage("Minimum Qty is not met : {$cartItem->getSku()}, Qty: {$cartItem->getMinQty()}");
                return false;
            }

            if (!$maxQtyMet) {
                $event->addErrorMessage("Insufficient stock level : {$cartItem->getSku()}, Available: {$cartItem->getAvailQty()}");
                return false;
            }
        }

        return true;
    }

    /**
     * @param $cartItem
     * @return $this
     */
    public function updateTierPrice(&$cartItem)
    {
        if (!$cartItem || !is_object($cartItem)) {
            return $this;
        }

        $qty = $cartItem->getQty();

        if ($cartItem->getTierPrices()
            && is_array($cartItem->getTierPrices())
        ) {

            $meetsTier = false;
            $tierPrices = $cartItem->getTierPrices();
            if ($tierPrices) {
                $lastQty = 0;
                foreach($tierPrices as $tierPrice) {

                    if ($tierPrice instanceof \stdClass) {
                        $tierPrice = get_object_vars($tierPrice);
                    }

                    if (is_array($tierPrice)) {
                        $tierPrice = new ArrayWrapper($tierPrice);
                    }

                    if ($qty > $lastQty && $qty >= $tierPrice->getQty()) {
                        $lastQty = $tierPrice->getQty();
                        $cartItem->setPrice($tierPrice->getPrice());
                        $this->setHasTierPriceChange(true);
                        $meetsTier = true;
                    }
                }
            }

            if (!$meetsTier) {
                $cartItem->setPrice($cartItem->getOrigPrice());
                $this->setHasTierPriceChange(true);
            }
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function saveCartItem()
    {

        $cartEntity = $this->initCartEntity()->getCartEntity();
        $cartItem = $this->getCartItem();

        $customerAddressId = $cartItem->get('customer_address_id', 'main');
        if (is_numeric($customerAddressId)) {
            $customerAddressId = (int) $customerAddressId;
        } elseif (is_int(strpos($customerAddressId, '_'))) {

            $addressParts = explode('_', $cartItem->getCustomerAddressId()); // eg 'address_3'
            $customerAddressId = count($addressParts) == 2
                ? $addressParts[1]
                : null;

        } elseif ($customerAddressId == 'main') {
            $customerAddressId = null;
        }

        $currencyService = $this->getCartSessionService()->getCurrencyService();
        $baseCurrency = $this->getCartSessionService()->getBaseCurrency();
        $customerCurrency = $this->getCartSessionService()->getCurrency();
        if (!strlen($customerCurrency)) {
            $customerCurrency = $baseCurrency;
        }

        $cartItemEntity = $this->getEntityService()->getInstance(EntityConstants::CART_ITEM);

        // get or create cartItemEntity

        if ($cartItem->getId()) {

            $cartItemEntity = $this->getEntityService()->find(EntityConstants::CART_ITEM, $cartItem->getId());

            if (!$cartItemEntity) {
                throw new \Exception("Something terrible happened.");
            }

            // update customer_address_id , if necessary
            if ($cartItemEntity->getCustomerAddressId() != $customerAddressId) {
                $cartItemEntity->setCustomerAddressId($customerAddressId);
            }

        } else {

            $itemJson = $cartItem
                ? $cartItem->toJson()
                : '{}';

            $cartItemEntity
                ->setCart($cartEntity)
                ->setCreatedAt(new \DateTime('now'))
                ->setSku($cartItem->getSku())
                ->setProductId($cartItem->getProductId())
                ->setQty($cartItem->getQty())
                ->setWeight($cartItem->getWeight())
                ->setWeightUnit($cartItem->getWeightUnit())
                ->setWidth($cartItem->getWidth())
                ->setHeight($cartItem->getHeight())
                ->setLength($cartItem->getLength())
                ->setMeasureUnit($cartItem->getMeasureUnit())
                ->setCustomerAddressId($customerAddressId)
                ->setSourceAddressKey($cartItem->getSourceAddressKey())
                ->setJson($itemJson);
        }

        if ($cartItemEntity) {

            $this->setCartItemEntity($cartItemEntity);

            $cartItemEntity->setQty($cartItem->getQty());

            // if existing row with price change, or inserting new row
            if (
                ($cartItemEntity->getId() && $this->getHasTierPriceChange())
                || !$cartItemEntity->getId()
            ) {

                $productCurrency = $cartItem->getCurrency();
                if (!$productCurrency) {
                    $productCurrency = $baseCurrency;
                }

                if ($baseCurrency == $productCurrency) {
                    if ($customerCurrency == $baseCurrency) {

                        $cartItemEntity->setPrice($cartItem->getPrice())
                            //->setTax() todo : update this during total collection, tax collector, create a tax grid function
                            //->setDiscount() todo : update this during total collection, retrieve from the discount grid function
                            ->setCurrency($baseCurrency)
                            ->setBasePrice($cartItem->getPrice())
                            //->setBaseTax() todo
                            //->setBaseDiscount() todo
                            ->setBaseCurrency($baseCurrency);

                    } else {

                        $cartItemEntity->setPrice($currencyService->convert($cartItem->getPrice(), $customerCurrency))
                            //->setTax() todo
                            //->setDiscount() todo
                            ->setCurrency($customerCurrency)
                            ->setBasePrice($cartItem->getPrice())
                            //->setBaseTax() todo
                            //->setBaseDiscount() todo
                            ->setBaseCurrency($baseCurrency);

                    }
                } else {
                    if ($productCurrency == $customerCurrency) {

                        $cartItemEntity->setPrice($cartItem->getPrice())
                            //->setTax() todo
                            //->setDiscount() todo
                            ->setCurrency($customerCurrency)
                            ->setBasePrice($currencyService->convert($cartItem->getPrice(), $baseCurrency, $customerCurrency))
                            //->setBaseTax() todo
                            //->setBaseDiscount() todo
                            ->setBaseCurrency($baseCurrency);

                    } else {

                        $cartItemEntity->setPrice($currencyService->convert($cartItem->getPrice(), $customerCurrency, $productCurrency))
                            //->setTax() todo
                            //->setDiscount() todo
                            ->setCurrency($customerCurrency)
                            ->setBasePrice($currencyService->convert($cartItem->getPrice(), $baseCurrency, $productCurrency))
                            //->setBaseTax() todo
                            //->setBaseDiscount() todo
                            ->setBaseCurrency($baseCurrency);
                    }
                }
            }

            try {
                $this->getEntityService()->persist($cartItemEntity);
                $this->setSuccess(true);
                $cartItem->setId($cartItemEntity->getId());
                $this->setCartItemEntity($cartItemEntity);
            } catch(\Exception $e) {

            }
        }

        return $this;
    }

    /**
     * @param $event
     * @param $cartItem
     * @param array $recollectShipping
     * @return $this
     */
    public function collectAddresses($event, &$cartItem, array &$recollectShipping)
    {
        $productId = $cartItem->getProductId();
        $request = $event->getRequest();
        $productAddresses = $request->get('product_address', []);

        // update shipping address
        if ($event->get('is_multi_shipping_enabled')) {

            // get customer_address_id from request
            if (isset($productAddresses[$productId])) {

                $customerAddressId = $productAddresses[$productId];
                if ($customerAddressId != 'main' && is_numeric($customerAddressId)) {
                    $customerAddressId = 'address_' . $customerAddressId;
                }

                if ($cartItem->get('customer_address_id', 'main') != $customerAddressId) {

                    // recollect the original shipping address, since the items have changed for that address
                    $recollectShipping[] = new ArrayWrapper([
                        'customer_address_id' => $cartItem->get('customer_address_id', 'main'),
                        'source_address_key' => $cartItem->get('source_address_key', 'main')
                    ]);

                    // update cart item
                    $cartItem->set('customer_address_id', $customerAddressId);
                }

                // recollect new shipping address
                $recollectShipping[] = new ArrayWrapper([
                    'customer_address_id' => $cartItem->get('customer_address_id', $customerAddressId),
                    'source_address_key' => $cartItem->get('source_address_key', 'main')
                ]);

            } else {

                // we can assume the address for the cart item is not changing
                //  but we need to recollect in case qty or weight changed

                $recollectShipping[] = new ArrayWrapper([
                    'customer_address_id' => $cartItem->get('customer_address_id', 'main'),
                    'source_address_key' => $cartItem->get('source_address_key', 'main')
                ]);
            }

        } else {

            // always recollect main address when multi shipping is disabled

            $recollectShipping[] = new ArrayWrapper([
                'customer_address_id' => $cartItem->get('customer_address_id', 'main'),
                'source_address_key' => $cartItem->get('source_address_key', 'main')
            ]);
        }

        return $this;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCartAddProduct(CoreEvent $event)
    {
        $this->setCartItem(null); // need to reset, because this is a Singleton
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $cart = $this->getCartSessionService()->getCart();

        /**
         * Strategy:
         *
         * 1. Get keys from Request or Event
         * 2. Determine Product Type eg Simple, Configurable
         * 3. Check if the sku is already in the cart
         * 4. Handle Product Type logic
         *
         * Simple:
         * 1. Ensure it exists
         *
         * Configurable:
         * 1. Ensure both child and parent exist
         * 2. Ensure they are related
         *
         */

        // First, get keys from Request
        $keyValue = $request->get('id', ''); // this could be a sku
        $keyField = $request->get('key', 'product_id');
        $qty = $request->get('qty', 1);

        // Second, check Event for keys, and use them if they exist
        if ($event->get('product_id')) {
            $keyValue = $event->get('product_id');
            $keyField = 'product_id';
            $qty = $event->get('qty');
        }

        // this is used in meetsCriteria()
        $this->setQty($qty);
        $this->setIsAdd((bool) $event->get('is_add'));

        $keyFields = ['id', 'product_id', 'sku']; // product.id , cart_item.product_id, product.sku, cart_item.sku
        if (!in_array($keyField, $keyFields)) {
            $keyField = 'product_id';
        }

        /** @var \MobileCart\CoreBundle\Entity\Product $product */
        $product = $this->loadProduct($keyValue, $keyField);
        if (!$product) {
            $event->addErrorMessage('Product not found');
            $event->setResponse(new RedirectResponse($this->getRouter()->generate('cart_view', [])));
            return;
        }

        $recollectShipping = []; // r = [object, object] , object:{'customer_address_id':'','source_address_key':''}
        switch($product->getType()) {
            case EntityConstants::PRODUCT_TYPE_CONFIGURABLE:

                $event->set('product_type', $product->getType());

                $simpleProductId = (int) $request->get('simple_id', 0)
                    ? $request->get('simple_id', 0)
                    : $product->getId();

                $item = $cart->findItem('product_id', $simpleProductId);
                if ($item) {
                    if ($this->meetsCriteria($item, $event)) {

                        $totalQty = $this->getIsAdd()
                            ? $item->getQty() + $this->getQty()
                            : $this->getQty();

                        $item->setQty($totalQty);

                        // update quantity
                        $this->getCartSessionService()
                            ->setProductQty($simpleProductId, $totalQty);

                        // update tier price
                        $this->updateTierPrice($item);

                        // update shipping address
                        $this->collectAddresses($event, $item, $recollectShipping);

                        // update cart item and totals
                        $this->setCartItem($item)->saveCartItem();

                        if ($simpleProductId) {
                            $event->setSimpleProductId($simpleProductId);
                        }

                        $this->setSuccess(true);
                    }
                } else {

                    $simpleProduct = $this->loadProduct($simpleProductId, 'id');
                    $parentOptions = $simpleProduct
                        ? [
                            'id' => $product->getId(),
                            'sku' => $product->getSku(),
                            'slug' => $product->getSlug(),
                          ]
                        : [];

                    $itemProduct = $simpleProduct
                        ? $simpleProduct
                        : $product;

                    $item = $this->getCartSessionService()
                        ->createCartItem($itemProduct, $parentOptions)
                        ->setQty(0) // this is set after meetsCriteria() passes
                        ->setQtyAvail($itemProduct->getQty())
                        ->setIsQtyManaged((bool) $itemProduct->getIsQtyManaged())
                        ->setCustomerAddressId('main');

                    if ($this->meetsCriteria($item, $event)) {

                        $item->setQty($this->getQty());

                        // update tier price
                        $this->updateTierPrice($item);

                        // add to cart
                        $this->getCartSessionService()->addItem($item, $this->getQty());

                        // update shipping address
                        $this->collectAddresses($event, $item, $recollectShipping);

                        // update cart totals
                        $this->setCartItem($item)->saveCartItem();

                        if ($simpleProductId) {
                            $event->setSimpleProductId($simpleProductId);
                        }

                        $this->setSuccess(true);
                    }
                }

                break;
            case EntityConstants::PRODUCT_TYPE_SIMPLE:
                $event->set('product_type', $product->getType());
                $item = $cart->findItem('product_id', $product->getId());
                if ($item) {
                    if ($this->meetsCriteria($item, $event)) {

                        $totalQty = $this->getIsAdd()
                            ? $item->getQty() + $this->getQty()
                            : $this->getQty();

                        $item->setQty($totalQty);

                        // update quantity
                        $this->getCartSessionService()
                            ->setProductQty($product->getId(), $totalQty);

                        // update tier price
                        $this->updateTierPrice($item);

                        // update shipping address
                        $this->collectAddresses($event, $item, $recollectShipping);

                        // update cart item and totals
                        $this->setCartItem($item)->saveCartItem();

                        $this->setSuccess(true);
                    }
                } else {

                    $item = $this->getCartSessionService()
                        ->createCartItem($product, [])
                        ->setQty(0) // this is set after meetsCriteria() passes
                        ->setQtyAvail($product->getQty())
                        ->setIsQtyManaged((bool) $product->getIsQtyManaged())
                        ->setCustomerAddressId('main');

                    if ($this->meetsCriteria($item, $event)) {

                        $item->setQty($this->getQty());

                        // update tier price
                        $this->updateTierPrice($item);

                        // add to cart
                        $this->getCartSessionService()->addItem($item, $this->getQty());

                        // update shipping address
                        $this->collectAddresses($event, $item, $recollectShipping);

                        // update cart totals
                        $this->setCartItem($item)->saveCartItem();

                        $this->setSuccess(true);
                    }
                }

                break;
            default:

                break;
        }

        $event->set('product_id', $product->getId())
            ->set('cart_entity', $this->getCartEntity())
            ->set('recollect_shipping', $recollectShipping) // this is handled in UpdateTotalsShipping
            ->set('cart_item_entity', $this->getCartItemEntity())
            ->set('cart_item', $this->getCartItem())
            ->setReturnData('cart', $this->getCartSessionService()->getCart())
            ->setReturnData('success', (bool) $this->getSuccess());

        switch($format) {
            case 'json':
                $event->setResponse(new JsonResponse($event->getReturnData()));
                break;
            default:
                $route = 'cart_view';
                $params = [];
                if (!$event->getIsMassUpdate()) {
                    if ($this->getSuccess()) {
                        $event->addSuccessMessage('Product Added to Cart');
                    }
                    if (!$this->getRedirectToCart()) {
                        $route = 'cart_product_view';
                        $params = ['slug' => $product->getSlug()];
                    }
                }
                $event->setResponse(new RedirectResponse($this->getRouter()->generate($route, $params)));
                break;
        }
    }
}
