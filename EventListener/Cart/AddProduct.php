<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class AddProduct
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class AddProduct
{
    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

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
    protected $enableQtyCheck = true;

    /**
     * @var int
     */
    protected $qty = 1;

    /**
     * @var bool
     */
    protected $success = false;

    /**
     * @return \MobileCart\CoreBundle\Service\DoctrineEntityService
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
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
     * @param $item
     * @param CoreEvent $event
     * @return bool
     */
    public function meetsCriteria(\MobileCart\CoreBundle\CartComponent\Item $item, CoreEvent &$event)
    {
        $minQty = (int) $item->getMinQty();
        $availQty = $item->getAvailQty();
        $isQtyManaged = $item->getIsQtyManaged();

        $minQtyMet = $minQty == 0 || ($minQty > 0 && $item->getQty() >= $minQty);
        $maxQtyMet = !$isQtyManaged || ($isQtyManaged && $item->getQty() < $availQty);

        if (!$item->getIsEnabled()) {
            $event->addErrorMessage("Product is not enabled : {$item->getSku()}");
            return false;
        }

        if (!$item->getIsInStock()) {
            $event->addErrorMessage("Product is not in stock : {$item->getSku()}");
            return false;
        }

        if ($item->getPromoQty() > 0 && $item->getQty() !== $item->getPromoQty()) {
            $event->addInfoMessage('Cannot change qty on promo item');
            return false;
        }

        if ($this->getEnableQtyCheck()) {

            if (!$minQtyMet) {
                $event->addErrorMessage("Minimum Qty is not met : {$item->getSku()}, Qty: {$item->getMinQty()}");
                return false;
            }

            if (!$maxQtyMet) {
                $event->addErrorMessage("Insufficient stock level : {$item->getSku()}, Available: {$item->getAvailQty()}");
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
        if ($this->getCartService()->getIsMultiShippingEnabled()) {

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
        $request = $event->getRequest();
        $format = $request->get(\MobileCart\CoreBundle\Constants\ApiConstants::PARAM_RESPONSE_TYPE, '');
        $event->set('format', $format);
        $cart = $this->getCartService()->initCart()->getCart();

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
        $keyFields = ['id', 'product_id', 'sku']; // product.id , cart_item.product_id, product.sku, cart_item.sku
        if (!in_array($keyField, $keyFields)) {
            $keyField = 'product_id';
        }

        $qty = (int) $request->get('qty', 1);

        // Second, check Event for keys, and use them if they exist
        if ($event->get('product_id')) {
            $keyValue = $event->get('product_id');
            $keyField = 'product_id';
            $qty = (int) $event->get('qty');
        }

        // this is used in meetsCriteria()

        $this->setQty($qty);
        $this->setIsAdd((bool) $event->get('is_add'));

        /** @var \MobileCart\CoreBundle\Entity\Product $product */
        $product = $this->loadProduct($keyValue, $keyField);
        if (!$product) {
            $event->addErrorMessage('Product not found');
            return;
        }

        $simpleProductId = (int) $request->get('simple_id', 0)
            ? $request->get('simple_id', 0)
            : $product->getId();

        $recollectShipping = $event->get('recollect_shipping', []); // r = [object, object] , object:{'customer_address_id':'','source_address_key':''}
        switch($product->getType()) {
            case EntityConstants::PRODUCT_TYPE_CONFIGURABLE:

                $event->set('product_type', $product->getType());

                $item = $cart->findItem('product_id', $simpleProductId);
                if ($item) {

                    $origQty = $item->getQty();

                    $totalQty = $this->getIsAdd()
                        ? $origQty + $this->getQty()
                        : $this->getQty();

                    $item->setQty($totalQty);

                    if ($this->meetsCriteria($item, $event)) {

                        // update quantity
                        $this->getCartService()
                            ->setProductQty($simpleProductId, $totalQty)
                            ->updateItemEntityQty($product->getId(), $totalQty);

                        // update tier price
                        $this->updateTierPrice($item);

                        $this->collectAddresses($event, $item, $recollectShipping);

                        if ($simpleProductId) {
                            $event->setSimpleProductId($simpleProductId);
                        }

                        $this->setSuccess(true);
                    } else {
                        $item->setQty($origQty);
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

                    $item = $this->getCartService()->convertProductToItem($itemProduct, $parentOptions, $this->getQty());
                    if ($this->meetsCriteria($item, $event)) {

                        // update tier price
                        $this->updateTierPrice($item);

                        $this->getCartService()->addItem($item);
                        $itemEntity = $this->getCartService()->convertItemToEntity($item);
                        $this->getCartService()->addNewCartItemEntity($itemEntity);

                        $this->collectAddresses($event, $item, $recollectShipping);

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

                    $origQty = $item->getQty();

                    $totalQty = $this->getIsAdd()
                        ? $item->getQty() + $this->getQty()
                        : $this->getQty();

                    $item->setQty($totalQty);

                    if ($this->meetsCriteria($item, $event)) {

                        // update tier price
                        $this->updateTierPrice($item);

                        // update quantity
                        $this->getCartService()
                            ->setProductQty($product->getId(), $totalQty)
                            ->updateItemEntityQty($product->getId(), $totalQty);

                        $this->collectAddresses($event, $item, $recollectShipping);

                        $this->setSuccess(true);
                    } else {
                        $item->setQty($origQty);
                    }
                } else {

                    $item = $this->getCartService()->convertProductToItem($product, [], $this->getQty());
                    if ($this->meetsCriteria($item, $event)) {

                        // update tier price
                        $this->updateTierPrice($item);

                        $this->getCartService()->addItem($item);

                        $itemEntity = $this->getCartService()->convertItemToEntity($item);
                        $this->getCartService()->addNewCartItemEntity($itemEntity);

                        $this->collectAddresses($event, $item, $recollectShipping);

                        $this->setSuccess(true);
                    }
                }

                break;
            default:

                break;
        }

        $event->set('product_id', $product->getId())
            ->set('recollect_shipping', $recollectShipping) // this is handled in UpdateTotalsShipping
            ->setReturnData('cart', $this->getCartService()->getCart())
            ->setReturnData('success', (bool) $this->getSuccess());

        if (!$event->getMessages()
            && $this->getSuccess()
            && !$event->getIsMassUpdate()
        ) {
            $event->addSuccessMessage('Product Added to Cart');
        }
    }
}
