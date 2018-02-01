<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Service;

use MobileCart\CoreBundle\Constants\EntityConstants;
use MobileCart\CoreBundle\Shipping\Rate;
use MobileCart\CoreBundle\Shipping\RateRequest;
use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\CartComponent\Cart;
use MobileCart\CoreBundle\CartComponent\Item;
use MobileCart\CoreBundle\CartComponent\Customer;
use MobileCart\CoreBundle\CartComponent\CustomerAddress;
use MobileCart\CoreBundle\CartComponent\Shipment;
use MobileCart\CoreBundle\CartComponent\Discount;
use MobileCart\CoreBundle\Entity\Cart as CartEntity;
use MobileCart\CoreBundle\Entity\CartItem as CartItemEntity;
use MobileCart\CoreBundle\Entity\Customer as CustomerEntity;

/**
 * Class CartService
 * @package MobileCart\CoreBundle\Service
 *
 * This class provides a Singleton Shopping Cart as a Service
 */
class CartService
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var bool
     */
    protected $isAdminUser = false;

    /**
     * @var bool
     */
    protected $isApiRequest = false;

    /**
     * @var mixed
     */
    protected $session;

    /**
     * @var string
     */
    protected $sessionKey = 'cart';

    /**
     * @var CartEntity
     */
    protected $cartEntity;

    /**
     * @var array|CartItemEntity[]
     */
    protected $newCartItemEntities = [];

    /**
     * @var CustomerEntity
     */
    protected $customerEntity;

    /**
     * @var \MobileCart\CoreBundle\Service\CheckoutFormService
     */
    protected $checkoutFormService;

    /**
     * @var \MobileCart\CoreBundle\Service\ShippingService
     */
    protected $shippingService;

    /**
     * @var \MobileCart\CoreBundle\Service\DiscountService
     */
    protected $discountService;

    /**
     * @var \MobileCart\CoreBundle\Service\TaxService
     */
    protected $taxService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartTotalService
     */
    protected $cartTotalService;

    /**
     * @var \MobileCart\CoreBundle\Service\GeographyService
     */
    protected $geographyService;

    /**
     * @var array
     */
    protected $allowedCountryIds = [];

    /**
     * @var bool
     */
    protected $allowGuestCheckout = false;

    /**
     * Single page (1) or multi page form (0)
     *
     * @var int
     */
    protected $isSpaEnabled = true;

    /**
     * @param int $cartId
     * @return $this
     */
    public function setCartId($cartId)
    {
        $this->getCart()->setId((int) $cartId);
        return $this;
    }

    /**
     * @return int
     */
    public function getCartId()
    {
        return $this->getCart()->getId();
    }

    /**
     * @param $isAdminUser
     * @return $this
     */
    public function setIsAdminUser($isAdminUser)
    {
        $this->isAdminUser = (bool) $isAdminUser;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAdminUser()
    {
        return (bool) $this->isAdminUser;
    }

    /**
     * @param $isApiRequest
     * @return $this
     */
    public function setIsApiRequest($isApiRequest)
    {
        $this->isApiRequest = (bool) $isApiRequest;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsApiRequest()
    {
        return (bool) $this->isApiRequest;
    }

    /**
     * @param $session
     * @return $this
     */
    public function setSession($session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param $sessionKey
     * @return $this
     */
    public function setSessionKey($sessionKey)
    {
        $this->sessionKey = $sessionKey;
        return $this;
    }

    /**
     * @return string
     */
    public function getSessionKey()
    {
        return $this->sessionKey;
    }

    /**
     * @param Cart $cart
     * @return $this
     */
    public function setSessionCart(Cart $cart)
    {
        $this->session->set($this->getSessionKey(), $cart);
        return $this;
    }

    /**
     * @return Cart
     */
    public function getSessionCart()
    {
        return $this->session->get($this->getSessionKey());
    }

    /**
     * @return $this
     */
    public function updateSessionCart()
    {
        if (!$this->getIsApiRequest()) {
            $this->session->set($this->getSessionKey(), $this->getCart());
        }
        return $this;
    }

    /**
     * @param \MobileCart\CoreBundle\Service\CartTotalService $cartTotalService
     * @return $this
     */
    public function setCartTotalService($cartTotalService)
    {
        $this->cartTotalService = $cartTotalService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\CartTotalService
     */
    public function getCartTotalService()
    {
        return $this->cartTotalService;
    }

    /**
     * @param \MobileCart\CoreBundle\Service\ShippingService $shippingService
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
     * @param CheckoutFormService $checkoutFormService
     * @return $this
     */
    public function setCheckoutFormService(\MobileCart\CoreBundle\Service\CheckoutFormService $checkoutFormService)
    {
        $this->checkoutFormService = $checkoutFormService;
        return $this;
    }

    /**
     * @return CheckoutFormService
     */
    public function getCheckoutFormService()
    {
        return $this->checkoutFormService;
    }

    /**
     * @return bool
     */
    public function getIsMultiShippingEnabled()
    {
        return $this->getShippingService()->getIsMultiShippingEnabled();
    }

    /**
     * @param \MobileCart\CoreBundle\Service\DiscountService $discountService
     * @return $this
     */
    public function setDiscountService($discountService)
    {
        $this->discountService = $discountService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\DiscountService
     */
    public function getDiscountService()
    {
        return $this->discountService;
    }

    /**
     * @return AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->getDiscountService()->getEntityService();
    }

    /**
     * @param \MobileCart\CoreBundle\Service\TaxService $taxService
     * @return $this
     */
    public function setTaxService($taxService)
    {
        $this->taxService = $taxService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\TaxService
     */
    public function getTaxService()
    {
        return $this->taxService;
    }

    /**
     * @return CurrencyService
     */
    public function getCurrencyService()
    {
        return $this->getCartTotalService()->getCurrencyService();
    }

    /**
     * @param array $totals
     * @return $this
     */
    public function setTotals(array $totals)
    {
        $this->getCart()->setTotals($totals); // for saving state
        return $this;
    }

    /**
     * @return array
     */
    public function getTotals()
    {
        return $this->getCart()->getTotals();
    }

    /**
     * @param $key
     * @return bool
     */
    public function getTotal($key)
    {
        return $this->getCart()->getTotal($key);
    }

    /**
     * @param array $methodCodes
     * @return $this
     */
    public function setPaymentMethodCodes(array $methodCodes)
    {
        $this->getCart()->setPaymentMethodCodes($methodCodes);
        return $this;
    }

    /**
     * @param \MobileCart\CoreBundle\Service\GeographyService $geographyService
     * @return $this
     */
    public function setGeographyService($geographyService)
    {
        $this->geographyService = $geographyService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\GeographyService
     */
    public function getGeographyService()
    {
        return $this->geographyService;
    }

    /**
     * @param $countryIdsStr
     * @return $this
     */
    public function setAllowedCountryIds($countryIdsStr)
    {
        $countryIds = explode(',', $countryIdsStr);
        $this->allowedCountryIds = array_map('trim', $countryIds);
        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedCountryIds()
    {
        return $this->allowedCountryIds;
    }

    /**
     * @param $countryId
     * @return bool
     */
    public function isAllowedCountryId($countryId)
    {
        return in_array($countryId, $this->allowedCountryIds);
    }

    /**
     * @return array
     */
    public function getCountryRegions()
    {
        return $this->getGeographyService()->getRegionsByCountries($this->getAllowedCountryIds());
    }

    /**
     * @param bool $yesNo
     * @return $this
     */
    public function setAllowGuestCheckout($yesNo)
    {
        $this->allowGuestCheckout = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getAllowGuestCheckout()
    {
        return $this->allowGuestCheckout;
    }

    /**
     * @param $isEnabled
     * @return $this
     */
    public function setIsSpaEnabled($isEnabled)
    {
        $this->isSpaEnabled = $isEnabled;
        return $this;
    }

    /**
     * @return int
     */
    public function getIsSpaEnabled()
    {
        return $this->isSpaEnabled;
    }

    /**
     * @return Cart
     */
    public function getCartInstance()
    {
        $cart = new Cart();
        $cart->setCurrency($this->getBaseCurrency());
        return $cart;
    }

    /**
     * @return Customer
     */
    public function getCustomerInstance()
    {
        return new Customer();
    }

    /**
     * @return Item
     */
    public function getItemInstance()
    {
        return new Item();
    }

    /**
     * @return Discount
     */
    public function getDiscountInstance()
    {
        return new Discount();
    }

    /**
     * @return Shipment
     */
    public function getShipmentInstance()
    {
        return new Shipment();
    }

    /**
     * @param CartEntity $entity
     * @return Cart
     */
    public function convertCartEntity(CartEntity $entity)
    {
        // there is much more information in the json
        // this is one of the main features of MC: import/export json
        $cart = $this->getCartInstance();
        $cart->fromJson($entity->getJson());
        $cart->setId($entity->getId());

        if (!$cart->getCurrency()) {
            $cart->setCurrency($this->getCurrencyService()->getBaseCurrency());
        }

        return $cart;
    }

    /**
     * @param \MobileCart\CoreBundle\Entity\Product $product
     * @param array $parentOptions
     * @param int $qty
     * @return Item
     */
    public function convertProductToItem(\MobileCart\CoreBundle\Entity\Product $product, array $parentOptions = [], $qty = 1)
    {
        $item = $this->getItemInstance();

        $data = $product->getData();
        $data['product_id'] = $data['id'];
        unset($data['id']);

        $item->fromArray($data);

        $price = $product->getPrice();
        if ($this->getBaseCurrency() != $this->getCurrency()) {
            $price = $this->getCurrencyService()->convert($price, $this->getCurrency());
        }

        $item->setBaseCurrency($this->getBaseCurrency())
            ->setBasePrice($product->getPrice())
            ->setBaseTax(0)
            ->setBaseDiscount(0)
            ->setCurrency($this->getCurrency())
            ->setPrice($price)
            ->setTax(0)
            ->setAvailQty($product->getQty())
            ->setQty($qty)
            ->setDiscount(0);

        $item->setCategoryIds($product->getCategoryIds());

        if ($parentOptions) {
            $item->setParentOptions($parentOptions);
        }

        if ($pimages = $product->getImages()) {

            $images = [];
            foreach($pimages as $image) {
                $images[] = $image->getData();
            }
            $item->setImages($images);
        }

        if ($tierPrices = $product->getTierPrices()) {

            $tierData = [];
            foreach($tierPrices as $tierPrice) {

                $tierData[] = [
                    'qty' => $tierPrice->getQty(),
                    'price' => $tierPrice->getPrice(),
                ];
            }

            $item->setTierPrices($tierData)
                ->setOrigPrice($product->getPrice());
        }

        $item->setQty($qty);
        $item->setCustomerAddressId('main');

        return $item;
    }

    /**
     * @param Item $item
     * @return CartItemEntity
     */
    public function convertItemToEntity(Item $item)
    {
        /** @var CartItemEntity $cartItem */
        $cartItem = $this->getEntityService()->getInstance(EntityConstants::CART_ITEM);

        if ($item->getId()) {
            $cartItem->setId($item->getId());
        }

        if ($this->getCartEntity()) {
            $cartItem->setCart($this->getCartEntity());
        }

        $cartItem->setProductId($item->getProductId())
            ->setCreatedAt(new \DateTime('now'))
            ->setSku($item->getSku())
            ->setCurrency($item->getCurrency())
            ->setBaseCurrency($item->getBaseCurrency())
            ->setPrice($item->getPrice())
            ->setBasePrice($item->getPrice())
            ->setTax($item->getTax())
            ->setBaseTax($item->getBaseTax())
            ->setDiscount($item->getDiscount())
            ->setBaseDiscount($item->getBaseDiscount())
            ->setQty($item->getQty())
            ->setWeight($item->getWeight())
            ->setWeightUnit($item->getWeightUnit())
            ->setWidth($item->getWidth())
            ->setHeight($item->getHeight())
            ->setLength($item->getLength())
            ->setMeasureUnit($item->getMeasureUnit())
            ->setJson($item->toJson())
            ->setCustomerAddressId($item->getCustomerAddressId())
            ->setSourceAddressKey($item->getSourceAddressKey())
            ->setCustom($item->getCustom());

        return $cartItem;
    }

    /**
     * @param $productId
     * @param $qty
     * @return $this
     */
    public function updateItemEntityQty($productId, $qty)
    {
        $this->initCartEntity();
        if ($this->getCartEntity() && $this->getCartEntity()->getCartItems()) {
            $found = false;
            foreach($this->getCartEntity()->getCartItems() as $entity) {
                if ($productId != $entity->getProductId()) {
                    continue;
                }

                $entity->setQty($qty);
                $this->getEntityService()->persist($entity);
                $found = true;
                break;
            }

            if (!$found && $this->getNewCartItemEntities()) {
                foreach($this->getNewCartItemEntities() as $entity) {
                    if ($productId != $entity->getProductId()) {
                        continue;
                    }

                    $entity->setQty($qty);
                }
            }
        }
        return $this;
    }

    /**
     * @param $productId
     * @param array $params
     * @return $this
     */
    public function updateItemEntity($productId, array $params)
    {
        $this->initCartEntity();
        if ($this->getCartEntity() && $this->getCartEntity()->getCartItems()) {
            foreach($this->getCartEntity()->getCartItems() as $entity) {

                if ($productId != $entity->getProductId()) {
                    continue;
                }

                foreach($params as $key => $value) {
                    $entity->set($key, $value);
                }

                $this->getEntityService()->persist($entity);
                break;
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function resetCart()
    {
        $this->setCart($this->getCartInstance());
        return $this;
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        $this->initCart();

        if ($this->getIsApiRequest()) {
            return $this->cart;
        }

        return $this->getSessionCart();
    }

    /**
     * @param Cart $cart
     * @return $this
     */
    public function setCart(Cart $cart)
    {
        if ($this->getIsApiRequest()) {
            $this->cart = $cart;
        } else {
            $this->setSessionCart($cart);
        }

        return $this;
    }

    /**
     * @param Cart $cart
     * @return $this
     */
    public function initCart(Cart $cart = null)
    {
        if ($cart instanceof Cart) {
            $this->setCart($cart);
            if ($this->getCustomerEntity()) {
                $this->setCustomer($this->convertCustomerEntity($this->getCustomerEntity()));
            }
            return $this;
        } elseif ($this->getIsApiRequest() && $this->cart instanceof Cart) {
            return $this;
        } elseif (!$this->getIsApiRequest() && $this->getSessionCart() instanceof Cart) {
            return $this;
        }

        $cart = $this->getCartInstance();
        if ($this->getCartEntity()) {
            $cart = $this->convertCartEntity($this->getCartEntity());
        } else {
            $cart->setCurrency($this->getCurrencyService()->getBaseCurrency());

            $totals = [];

            $itemTotal = new \MobileCart\CoreBundle\EventListener\Cart\ItemTotal();
            $itemTotal->setLabel('Items');
            $itemTotal->setIsAdd(true);
            $itemTotal->setValue(0);
            $totals[] = $itemTotal;

            if ($this->getShippingService()->getIsShippingEnabled()) {
                $shipmentTotal = new \MobileCart\CoreBundle\EventListener\Cart\ShipmentTotal();
                $shipmentTotal->setLabel('Shipments');
                $shipmentTotal->setIsAdd(true);
                $shipmentTotal->setValue(0);
                $totals[] = $shipmentTotal;
            }

            if ($this->getTaxService()->getIsTaxEnabled()) {
                $taxTotal = new \MobileCart\CoreBundle\EventListener\Cart\TaxTotal();
                $taxTotal->setLabel('Tax');
                $taxTotal->setIsAdd(true);
                $taxTotal->setValue(0);
                $totals[] = $taxTotal;
            }

            if ($this->getDiscountService()->getIsDiscountEnabled()) {
                $discountTotal = new \MobileCart\CoreBundle\EventListener\Cart\DiscountTotal();
                $discountTotal->setLabel('Discounts');
                $discountTotal->setValue(0);
                $totals[] = $discountTotal;
            }

            $grandTotal = new \MobileCart\CoreBundle\EventListener\Cart\GrandTotal();
            $grandTotal->setLabel('Grand Total');
            $grandTotal->setValue(0);
            $totals[] = $grandTotal;

            $cart->setTotals($totals);
        }

        $this->setCart($cart);

        if ($this->getCustomerEntity()) {
            $this->setCustomer($this->convertCustomerEntity($this->getCustomerEntity()));
        }

        return $this;
    }

    /**
     * @param string $json
     * @return $this
     */
    public function initCartJson($json)
    {
        $cart = $this->getCartInstance();
        $cart->importJson($json);
        $this->setCart($cart);
        return $this;
    }

    /**
     * @param CartEntity $cartEntity
     * @return $this
     */
    public function setCartEntity(CartEntity $cartEntity)
    {
        $this->cartEntity = $cartEntity;
        return $this;
    }

    /**
     * @return CartEntity
     */
    public function getCartEntity()
    {
        return $this->cartEntity;
    }

    /**
     * @return string
     */
    public function getPaymentMethodCode()
    {
        return $this->getCartEntity()->getPaymentMethodCode();
    }

    /**
     * @return array
     */
    public function getPaymentData()
    {
        return @ (array) json_decode($this->getCartEntity()->getPaymentInfo());
    }

    /**
     * @param CartItemEntity $entity
     * @return $this
     */
    public function addNewCartItemEntity(CartItemEntity $entity)
    {
        $this->newCartItemEntities[] = $entity;
        return $this;
    }

    /**
     * @return array|\MobileCart\CoreBundle\Entity\CartItem[]
     */
    public function getNewCartItemEntities()
    {
        return $this->newCartItemEntities;
    }

    /**
     * @param CartEntity $cartEntity
     * @return $this
     */
    public function initCartEntity(CartEntity $cartEntity = null)
    {
        if (is_null($cartEntity)) {

            if ($this->getCartEntity()) {
                return $this;
            }

            if ($this->getCart() && $this->getCart()->getId()) {
                $cartEntity = $this->getEntityService()->find(EntityConstants::CART, $this->getCart()->getId());
            }

            if (!$cartEntity) {
                $cartEntity = $this->createCartEntity();
                $cartEntity->setJson($this->getCart()->toJson());
            }

            $this->setCartEntity($cartEntity);

        } else {
            $this->setCartEntity($cartEntity);
        }

        if ($this->getCustomerEntity()) {
            $this->getCartEntity()->setCustomer($this->getCustomerEntity());
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function saveCart()
    {
        $this->updateCartEntity();
        $cartEntity = $this->getCartEntity();
        $isNew = !is_numeric($cartEntity->getId());
        $this->getEntityService()->persist($cartEntity);
        $this->setCartId($cartEntity->getId());

        if ($isNew || $this->getNewCartItemEntities()) {

            if ($this->getNewCartItemEntities()) {
                foreach ($this->getNewCartItemEntities() as $cartItemEntity) {
                    $cartItemEntity->setCart($cartEntity);
                    $this->getEntityService()->persist($cartItemEntity);
                    $cartItem = $this->getCart()->findItem('product_id', $cartItemEntity->getProductId());
                    if ($cartItem) {
                        $cartItem->setId($cartItemEntity->getId());
                        $cartItemEntity->setJson($cartItem->toJson());
                        $this->getEntityService()->persist($cartItemEntity);
                    }
                }
            }

            // save latest IDs and json
            $cartEntity->setJson($this->getCart()->toJson());
            $this->getEntityService()->persist($cartEntity);
        }

        $this->setCartEntity($cartEntity);
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function saveCustomerEntity()
    {
        if (!$this->getCustomerEntity()) {
            throw new \Exception('Customer entity is not set');
        }

        $this->getEntityService()->persist($this->getCustomerEntity());
        if (!$this->getCustomerId()
            && $this->getCustomerEntity()
            && $this->getCustomerEntity()->getId()
        ) {
            $this->getCustomer()->setId($this->getCustomerEntity()->getId());
        }

        return $this;
    }

    /**
     * @return array
     */
    public function initCheckoutState()
    {
        $sections = [];
        $sectionKeys = $this->getCheckoutFormService()->getSectionKeys();
        if ($sectionKeys) {
            foreach($sectionKeys as $sectionKey) {
                $sections[$sectionKey] = false;
            }
        }

        return $sections;
    }

    /**
     * @return array
     */
    public function getCheckoutState()
    {
        $this->initCartEntity();
        return @ (array) json_decode($this->getCartEntity()->getCheckoutState());
    }

    /**
     * @param string $section
     * @param bool $isValid
     * @return $this
     */
    public function setSectionIsValid($section, $isValid = true)
    {
        $sections = $this->getCheckoutState();
        $sections[$section] = $isValid;
        $this->getCartEntity()->setCheckoutState(json_encode($sections));
        return $this;
    }

    /**
     * @param $section
     * @return bool
     */
    public function getSectionIsValid($section)
    {
        $sections = $this->getCheckoutState();
        return isset($sections[$section])
            ? (bool) $sections[$section]
            : false;
    }

    /**
     * @return array
     */
    public function getInvalidSections()
    {
        $invalid = [];
        $sections = $this->getCheckoutState();
        if ($sections) {
            foreach($sections as $section => $isValid) {
                if (!$isValid) {
                    $invalid[] = $section;
                }
            }
        }

        return $invalid;
    }

    /**
     * @return bool
     */
    public function getIsAllValid()
    {
        return count($this->getInvalidSections()) == 0;
    }

    /**
     * @return CartEntity
     */
    public function createCartEntity()
    {
        /** @var CartEntity $cartEntity */
        $cartEntity = $this->getEntityService()->getInstance(EntityConstants::CART);
        $cartEntity->setCreatedAt(new \DateTime('now'))
            ->setCurrency($this->getCurrency())
            ->setBaseCurrency($this->getBaseCurrency());

        if ($this->getCustomerEntity()) {
            $cartEntity->setCustomer($this->getCustomerEntity());
        }

        $cartEntity->setCheckoutState(json_encode($this->initCheckoutState()));

        return $cartEntity;
    }

    /**
     * @return $this
     */
    public function updateCartEntity()
    {
        $this->updateTotals();
        $this->getCartEntity()->setJson($this->getCart()->toJson());
        return $this;
    }

    /**
     * @return $this
     */
    public function updateTotals()
    {
        $this->initCartEntity();
        $this->collectTotals();
        $currency = $this->getCurrency();
        $baseCurrency = $this->getBaseCurrency();

        foreach($this->getCart()->getTotals() as $total) {
            switch($total->getKey()) {
                case \MobileCart\CoreBundle\EventListener\Cart\ItemTotal::KEY:
                    $this->getCartEntity()->setBaseItemTotal($total->getValue());
                    if ($baseCurrency == $currency) {
                        $this->getCartEntity()->setItemTotal($total->getValue());
                    } else {
                        $this->getCartEntity()->setItemTotal($this->getCurrencyService()->convert($total->getValue(), $currency));
                    }
                    break;
                case \MobileCart\CoreBundle\EventListener\Cart\ShipmentTotal::KEY:
                    $this->getCartEntity()->setBaseShippingTotal($total->getValue());
                    if ($baseCurrency == $currency) {
                        $this->getCartEntity()->setShippingTotal($total->getValue());
                    } else {
                        $this->getCartEntity()->setShippingTotal($this->getCurrencyService()->convert($total->getValue(), $currency));
                    }
                    break;
                case \MobileCart\CoreBundle\EventListener\Cart\TaxTotal::KEY:
                    $this->getCartEntity()->setBaseTaxTotal($total->getValue());
                    if ($baseCurrency == $currency) {
                        $this->getCartEntity()->setTaxTotal($total->getValue());
                    } else {
                        $this->getCartEntity()->setTaxTotal($this->getCurrencyService()->convert($total->getValue(), $currency));
                    }
                    break;
                case \MobileCart\CoreBundle\EventListener\Cart\DiscountTotal::KEY:
                    $this->getCartEntity()->setBaseDiscountTotal($total->getValue());
                    if ($baseCurrency == $currency) {
                        $this->getCartEntity()->setDiscountTotal($total->getValue());
                    } else {
                        $this->getCartEntity()->setDiscountTotal($this->getCurrencyService()->convert($total->getValue(), $currency));
                    }
                    break;
                case \MobileCart\CoreBundle\EventListener\Cart\GrandTotal::KEY:
                    $this->getCartEntity()->setBaseTotal($total->getValue());
                    if ($baseCurrency == $currency) {
                        $this->getCartEntity()->setTotal($total->getValue());
                    } else {
                        $this->getCartEntity()->setTotal($this->getCurrencyService()->convert($total->getValue(), $currency));
                    }
                    break;
                default:
                    // no-op
                    break;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function copyCartEntityToCart()
    {
        $this->initCartJson($this->getCartEntity()->getJson());
        $this->setCartId($this->getCartEntity()->getId());
        return $this;
    }

    /**
     * @param $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->getCart()->setCurrency($currency);
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->getCart()->getCurrency();
    }

    /**
     * @return string
     */
    public function getBaseCurrency()
    {
        return $this->getCurrencyService()->getBaseCurrency();
    }

    /**
     * @param Item $item
     * @param int $qty
     * @return $this
     */
    public function addItem(Item $item, $qty = 1)
    {
        $item->setQty($qty);
        $this->getCart()->addItem($item);
        return $this;
    }

    /**
     * @return Item[]
     */
    public function getItems()
    {
        return $this->getCart()->getItems();
    }

    /**
     * @param $key
     * @param $value
     * @return Item|null
     */
    public function findItem($key, $value)
    {
        return $this->getCart()->findItem($key, $value);
    }

    /**
     * @return $this
     */
    public function removeItems()
    {
        $this->getCart()->unsetItems();
        $this->removeShipments();
        return $this;
    }

    /**
     * @param $productId
     * @return mixed
     */
    public function removeProductId($productId)
    {
        $this->getCart()->removeProductId($productId);
        if (!$this->getCart()->hasItems()) {
            $this->removeShipments();
        }
        return $this;
    }

    /**
     * @param $itemId
     * @return $this
     */
    public function removeItemId($itemId)
    {
        $this->getCart()->removeItemId($itemId);
        if (!$this->getCart()->hasItems()) {
            $this->removeShipments();
        }
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function removeItem($key, $value)
    {
        $this->getCart()->removeItem($key, $value);
        if (!$this->getCart()->hasItems()) {
            $this->removeShipments();
        }
        return $this;
    }

    /**
     * @param Discount $discount
     * @return $this
     */
    public function removeDiscount(Discount $discount)
    {
        $this->getCart()->removeDiscount($discount);
        return $this;
    }

    /**
     * @param CartItemEntity $cartItemEntity
     * @return $this
     */
    public function addItemEntity(CartItemEntity $cartItemEntity)
    {
        $this->initCartEntity();
        $this->getCartEntity()->addCartItem($cartItemEntity);
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function deleteItemEntity($key, $value)
    {
        $this->initCartEntity();
        $cartItems = $this->getCartEntity()->getCartItems();
        if ($cartItems) {
            /** @var CartItemEntity $cartItem */
            foreach($cartItems as $cartItem) {
                if ($cartItem->get($key) == $value) {
                    $this->getEntityService()->remove($cartItem);
                }
            }
        }
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function removeAndDeleteItem($key, $value)
    {
        $this->removeItem($key, $value);
        $this->deleteItemEntity($key, $value);
        return $this;
    }

    /**
     * @param $productId
     * @return bool
     */
    public function hasProductId($productId)
    {
        return is_numeric($this->getCart()->findItemIdx('product_id', $productId));
    }

    /**
     * @param $sku
     * @return bool
     */
    public function hasSku($sku)
    {
        return is_numeric($this->getCart()->findItemIdx('sku', $sku));
    }

    /**
     * @param \MobileCart\CoreBundle\Entity\Product $product
     * @param int $qty
     * @param array $parentOptions
     * @return $this
     */
    public function addProduct(\MobileCart\CoreBundle\Entity\Product $product, $qty = 1, $parentOptions = [])
    {
        $item = $this->convertProductToItem($product, $parentOptions);
        $this->addItem($item, $qty);
        return $this;
    }

    /**
     * @return array
     */
    public function getProductIds()
    {
        return $this->getCart()->getProductIds();
    }

    /**
     * Update qty on item already in cart
     *
     * @param $productId
     * @param $qty
     * @return $this
     */
    public function setProductQty($productId, $qty)
    {
        $this->getCart()->setProductQty($productId, $qty);
        if (!$this->getCart()->hasItems()) {
            $this->removeShipments();
        }
        return $this;
    }

    /**
     * Add qty on item already in cart
     *
     * @param $productId
     * @param $qty
     * @return $this
     */
    public function addProductQty($productId, $qty)
    {
        $this->getCart()->addProductQty($productId, $qty);
        return $this;
    }

    /**
     * @param Customer $customer
     * @return $this
     */
    public function setCustomer(Customer $customer)
    {
        // don't call getCart() here because initCart() calls this method
        if ($this->getIsApiRequest()) {
            $this->cart->setCustomer($customer);
        } else {
            $this->getSessionCart()->setCustomer($customer);
        }

        return $this;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->getCart()->getCustomer();
    }

    /**
     * @param $groupName
     * @return bool
     */
    public function customerHasGroup($groupName)
    {
        $groups = $this->getCustomer()->getGroups();
        if (!is_array($groups)) {
            $groups = [];
        }
        return in_array($groupName, $groups);
    }

    /**
     * @return bool
     */
    public function hasItems()
    {
        return (bool) $this->getCart()->hasItems();
    }

    /**
     * @return array|CustomerAddress[]
     */
    public function getCustomerAddresses()
    {
        return $this->getCustomer()->getAddresses();
    }

    /**
     * @param $addressId
     * @return string
     */
    public function prefixAddressId($addressId)
    {
        return $this->getCart()->prefixAddressId($addressId);
    }

    /**
     * @param $addressId
     * @return string
     */
    public function unprefixAddressId($addressId)
    {
        return $this->getCart()->unprefixAddressId($addressId);
    }

    /**
     * @param string $addressId
     * @return null
     */
    public function getCustomerAddress($addressId='main')
    {
        $addressId = $this->unprefixAddressId($addressId);
        $addresses = $this->getCustomerAddresses();
        if ($addresses) {
            foreach($addresses as $address) {
                if ($address->getId() == $addressId) {
                    return $address;
                }
            }
        }

        return null;
    }

    /**
     * @param string $addressId
     * @return string
     */
    public function addressLabel($addressId='main')
    {
        return $this->getCart()->addressLabel($addressId);
    }

    /**
     * @param CustomerEntity $entity
     * @return $this
     */
    public function setCustomerEntity(\MobileCart\CoreBundle\Entity\Customer $entity)
    {
        $this->customerEntity = $entity;
        return $this;
    }

    /**
     * @return CustomerEntity
     */
    public function getCustomerEntity()
    {
        return $this->customerEntity;
    }

    /**
     * @return $this
     */
    public function loadCustomerEntity()
    {
        if ($this->getCustomerId()) {
            $customerEntity = $this->getEntityService()->find(EntityConstants::CUSTOMER, $this->getCustomerId());
            if ($customerEntity) {
                $this->setCustomerEntity($customerEntity);
            }
        }

        return $this;
    }

    /**
     * @param CustomerEntity $entity
     * @return Customer
     */
    public function convertCustomerEntity(CustomerEntity $entity)
    {
        $customer = $this->getCustomerInstance();
        $customer->fromArray($entity->getData());
        $customer->setId($entity->getId());

        $addresses = [];
        $postcodes = [];
        $addressEntities = $entity->getAddresses();

        $address = new CustomerAddress();
        $address->setId('main')
            ->setCustomerId($entity->getId())
            ->setName($entity->getShippingName())
            ->setCompany($entity->getShippingCompany())
            ->setStreet($entity->getShippingStreet())
            ->setStreet2($entity->getShippingStreet2())
            ->setCity($entity->getShippingCity())
            ->setRegion($entity->getShippingRegion())
            ->setPostcode($entity->getShippingPostcode())
            ->setCountryId($entity->getShippingCountryId())
            ->setPhone($entity->getShippingPhone());

        $addresses[] = $address;
        $postcodes[] = $entity->getShippingPostcode();

        if ($addressEntities) {
            foreach($addressEntities as $addressEntity) {

                $address = new CustomerAddress();
                $address->setId($addressEntity->getId())
                    ->setCustomerId($entity->getId())
                    ->setName($addressEntity->getName())
                    ->setCompany($addressEntity->getCompany())
                    ->setStreet($addressEntity->getStreet())
                    ->setStreet2($addressEntity->getStreet2())
                    ->setCity($addressEntity->getCity())
                    ->setRegion($addressEntity->getRegion())
                    ->setPostcode($addressEntity->getPostcode())
                    ->setCountryId($addressEntity->getCountryId())
                    ->setPhone($addressEntity->getPhone());

                $addresses[] = $address;
                $postcodes[] = $addressEntity->getPostcode();
            }
        }
        $customer->setAddresses($addresses);

        $groups = $entity->getGroups();
        $groupNames = [];
        if ($groups) {
            foreach($groups as $group) {
                $groupNames[] = $group->getName();
            }
        }
        $customer->setGroups($groupNames);

        return $customer;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return ($this->getCustomer() instanceof Customer)
            ? $this->getCustomer()->getId()
            : 0;
    }

    /**
     * Adds a chosen shipping method
     * This activates shipping costs within shopping cart
     *  and allows for multiple shipping methods to be used within the cart
     *
     * @param Shipment $shipment
     * @return $this
     */
    public function addShipment(Shipment $shipment)
    {
        $this->getCart()->addShipment($shipment);
        return $this;
    }

    /**
     * @param string $addressId
     * @param string $srcAddressKey
     * @return bool
     */
    public function hasShipments($addressId='', $srcAddressKey='main')
    {
        return $this->getCart()->hasShipments($addressId, $srcAddressKey);
    }

    /**
     * @param $code
     * @param $addressId
     * @param $srcAddressKey
     * @return bool
     */
    public function hasShippingMethodCode($code, $addressId='main', $srcAddressKey='main')
    {
        return $this->getCart()->hasShippingMethodCode($code, $addressId, $srcAddressKey);
    }

    /**
     * @return string
     */
    public function getShipmentMethod()
    {
        $shipments = $this->getCart()->getShipments();
        if ($shipments) {
            $shipment = $shipments[0];
            return $shipment->getId();
        }

        return '';
    }

    /**
     * @return array
     */
    public function getAllShippingMethods()
    {
        return $this->getCart()->getAllShippingMethods();
    }

    /**
     * @param array $rates
     * @return $this
     */
    public function addRates(array $rates = [])
    {
        if ($rates) {
            foreach($rates as $code => $rate) {
                $this->addRate($rate);
            }
        }
        return $this;
    }

    /**
     * @param array $rates
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function setRates(array $rates = [], $addressId='main', $srcAddressKey='main')
    {
        $this->removeRates($addressId, $srcAddressKey);

        if (!$addressId) {
            $addressId = 'main';
        }

        if ($rates) {
            foreach($rates as $code => $rate) {
                $rate->set('customer_address_id', $addressId);
                $rate->set('source_address_key', $srcAddressKey);
                $this->addRate($rate, $addressId, $srcAddressKey);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function removeRates()
    {
        $this->getCart()->unsetShippingMethods();
        return $this;
    }

    /**
     * @param Rate $rate
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function addRate(Rate $rate, $addressId='main', $srcAddressKey='main')
    {
        $shipment = $this->getShipmentInstance();
        $shipment->fromArray($rate->toArray());
        $this->addShippingMethod($shipment, $addressId, $srcAddressKey);
        return $this;
    }

    /**
     * Add a shipping method _option_ to the cart
     * This does not activate shipping costs within the shopping cart
     * This is an estimated cost, and needs to be stored in the cart
     *  in order to avoid calculating possible shipping options on every page
     *
     * @param Shipment $shipment
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function addShippingMethod(Shipment $shipment, $addressId='main', $srcAddressKey='main')
    {
        $this->getCart()->addShippingMethod($shipment, $addressId, $srcAddressKey);
        return $this;
    }

    /**
     * @param $addressId
     * @param $srcAddressKey
     * @return array
     */
    public function getShippingMethods($addressId='main', $srcAddressKey='main')
    {
        return $this->getCart()->getShippingMethods($addressId, $srcAddressKey);
    }

    /**
     * Empty both shipments and shipment options
     *
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function removeShipments($addressId='', $srcAddressKey='main')
    {
        $this->getCart()->unsetShipments($addressId, $srcAddressKey);
        return $this;
    }

    /**
     *
     * @param $addressId
     * @param $srcAddressKey
     * @return $this
     */
    public function removeShippingMethods($addressId='', $srcAddressKey='main')
    {
        $this->getCart()->unsetShippingMethods($addressId, $srcAddressKey);
        return $this;
    }

    /**
     * @param string|int $key
     * @param bool $isKey
     * @return $this
     */
    public function removeShipment($key, $isKey = true)
    {
        $this->getCart()->unsetShipment($key, $isKey);
        return $this;
    }

    /**
     * @return $this
     */
    public function collectTotals()
    {
        $totals = $this->getCartTotalService()
            ->setIsShippingEnabled($this->getShippingService()->getIsShippingEnabled())
            ->setIsTaxEnabled($this->getTaxService()->getIsTaxEnabled())
            // ->setIsDiscountEnabled() // todo: wire this up
            ->setCart($this->getCart())
            ->collectTotals()
            ->getTotals();

        $this->getCart()->setTotals($totals); // for saving state
        return $this;
    }

    /**
     * @param array $addresses
     * @return $this
     */
    public function collectAddressShipments($addresses)
    {
        if (!is_array($addresses)) {
            return $this;
        }

        $collected = [];
        if ($addresses) {
            foreach($addresses as $recollectAddress) {

                if (!($recollectAddress instanceof ArrayWrapper)) {
                    continue;
                }

                $customerAddressId = $recollectAddress->get('customer_address_id', 'main');
                $srcAddressKey = $recollectAddress->get('source_address_key', 'main');
                $collectKey = "{$customerAddressId}-{$srcAddressKey}";
                if (in_array($collectKey, $collected)) {
                    continue;
                } else {
                    $collected[] = $collectKey;
                }

                $hasItems = false;
                if ($this->getCart()->hasItems()) {
                    foreach($this->getCart()->getItems() as $cartItem) {
                        if ($cartItem->getCustomerAddressId() == $customerAddressId
                            && $cartItem->getSourceAddressKey() == $srcAddressKey
                        ) {
                            $hasItems = true;
                        }
                    }
                }

                // remove shipments and shipping methods
                if (!$hasItems) {
                    $this->removeShipments($customerAddressId, $srcAddressKey);
                    $this->removeShippingMethods($customerAddressId, $srcAddressKey);
                    continue;
                }

                // shipment quotes are stored in the cart json . they don't have their own table, so we dont need to persist
                $this->collectShippingMethods($this->createRateRequest($customerAddressId, $srcAddressKey));
            }
        }

        return $this;
    }

    /**
     * @param string $addressId
     * @param string $srcAddressKey
     * @return RateRequest
     */
    public function createRateRequest($addressId='main', $srcAddressKey='main')
    {
        $addressId = $this->unprefixAddressId($addressId);

        $customer = $this->getCustomer();

        $cartItems = [];
        $addtlPrice = 0.0;
        if ($this->getCart()->hasItems()) {
            foreach($this->getCart()->getItems() as $item) {

                if ($item->getCustomerAddressId() == $addressId
                    && $item->getSourceAddressKey() == $srcAddressKey
                ) {

                    /*
                    if ($item->getIsFlatShipping()) {
                        $addtlPrice += ($item->getQty() * (float) $item->getFlatShippingPrice());
                        continue;
                    }//*/

                    $cartItems[] = $item;
                }
            }
        }

        $rateRequest = $this->getShippingService()->createRateRequest($srcAddressKey, $cartItems, $addtlPrice);
        $rateRequest->setSourceAddressKey($srcAddressKey)
            ->setCustomerAddressId($addressId);

        // default to 'main' shipping address
        $postcode = $customer->getShippingPostcode();
        $countryId = $customer->getShippingCountryId();
        $region = $customer->getShippingRegion();

        // if the shipping address is stored in customer_address and has an ID
        if (is_numeric($addressId) && $customer->getAddresses()) {
            foreach($customer->getAddresses() as $address) {
                if ($address->getId() == $addressId) {
                    $postcode = $address->getPostcode();
                    $countryId = $address->getCountryId();
                    $region = $address->getRegion();
                    break;
                }
            }
        }

        $rateRequest->setDestPostcode($postcode)
            ->setDestCountryId($countryId)
            ->setDestRegion($region);

        return $rateRequest;
    }

    /**
     * @param $addressId
     * @param $srcAddressKey
     * @return bool
     */
    public function addressHasShipment($addressId, $srcAddressKey='main')
    {
        return $this->getCart()->addressHasShipment($addressId, $srcAddressKey);
    }

    /**
     * @param RateRequest $rateRequest
     * @return $this
     */
    public function collectShippingMethods(RateRequest $rateRequest)
    {
        $addressId = $rateRequest->getCustomerAddressId();
        $srcAddressKey = $rateRequest->getSourceAddressKey();

        // get current shipment method
        $currentShipment = $this->getCart()->getAddressShipment($addressId, $srcAddressKey);

        $this->removeShipments($addressId, $srcAddressKey)
            ->removeShippingMethods($addressId, $srcAddressKey);

        $rates = [];
        try {
            $rates = $this->getShippingService()->collectShippingRates($rateRequest);
        } catch(\Exception $e) {
            //$this->getLogger()->error("CartSession : reloadShipments() : Shipping Exception for Customer ID : {$customerId} : {$e->getMessage()}");
        }

        $this->setRates($rates, $addressId, $srcAddressKey);

        // add first rate as a shipment
        if (!$this->addressHasShipment($addressId, $srcAddressKey)) {
            if (count($rates)) {
                $rates = array_values($rates);
                /** @var \MobileCart\CoreBundle\Shipping\Rate $rate */
                $rate = $rates[0];
                if ($currentShipment) {
                    /** @var \MobileCart\CoreBundle\Shipping\Rate $aRate */
                    foreach($rates as $idx => $aRate) {
                        if ($aRate->getCode() == $currentShipment->getCode()) {
                            $rate = $rates[$idx];
                            break;
                        }
                    }
                }

                $shipment = new Shipment();
                $shipment->fromArray($rate->getData());
                $this->addShipment($shipment);
            }
        }

        return $this;
    }
}
