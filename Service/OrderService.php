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
use MobileCart\CoreBundle\CartComponent\ArrayWrapper;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\EventListener\Cart\DiscountTotal;
use MobileCart\CoreBundle\EventListener\Cart\GrandTotal;
use MobileCart\CoreBundle\EventListener\Cart\ItemTotal;
use MobileCart\CoreBundle\EventListener\Cart\ShipmentTotal;
use MobileCart\CoreBundle\EventListener\Cart\TaxTotal;
use MobileCart\CoreBundle\Payment\Exception\PaymentFailedException;
use MobileCart\CoreBundle\Payment\PaymentMethodServiceInterface;
use MobileCart\CoreBundle\Payment\TokenPaymentMethodServiceInterface;

/**
 * Class OrderService
 * @package MobileCart\CoreBundle\Service
 *
 * This class assumes an order can have multiple
 *  payments, invoices, refunds, etc
 * BUT it only creates 1 at a time
 * If you're creating multiple invoices for a single order
 *  it will swap out the invoice as they are created
 *
 */
class OrderService
{
    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * Passed into events, for event listeners
     *
     * @var array
     */
    protected $eventData = [];

    /**
     * @var AbstractEntityService
     */
    protected $entityService;

    /**
     * @var CartService
     */
    protected $cartService;

    /**
     * @var PaymentService
     */
    protected $paymentService;

    /**
     * @var PaymentMethodServiceInterface|TokenPaymentMethodServiceInterface
     */
    protected $paymentMethodService;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var CustomerToken
     */
    protected $customerToken;

    /**
     * @var bool
     */
    protected $enableEnableCreatePayment = false;

    /**
     * @var bool
     */
    protected $enableCreateInvoice = false;

    /**
     * @var bool
     */
    protected $enableCreateShipment = false;

    /**
     * @var bool
     */
    protected $detectFraudBeforeOrder = false;

    /**
     * @var bool
     */
    protected $detectFraud = false;

    /**
     * @var mixed
     */
    protected $fraudDetectionService;

    /**
     * @var int
     */
    protected $fraudRating = 0;

    /**
     * @var int
     */
    protected $fraudRatingTrigger = 5;

    /**
     * @var bool
     */
    protected $isFraud = false;

    /**
     * @var bool
     */
    protected $isRefund = false;

    /**
     * @var ArrayWrapper
     */
    protected $paymentInfo;

    /**
     * @var OrderPayment
     */
    protected $payment;

    /**
     * @var bool
     */
    protected $paymentSuccess = false;

    /**
     * @var string
     */
    protected $paymentMethodCode;

    /**
     * @var mixed
     */
    protected $paymentData;

    /**
     * @var OrderInvoice
     */
    protected $invoice;

    /**
     * @var OrderRefund
     */
    protected $refund;

    /**
     * @param $eventDispatcher
     * @return $this
     */
    public function setEventDispatcher($eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * @return EventDispatcher
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setEventData($key, $value)
    {
        $this->eventData[$key] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getEventData()
    {
        return $this->eventData;
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
     * @return mixed
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
     * @return CartService
     */
    public function getCartService()
    {
        return $this->cartService;
    }

    /**
     * @return mixed
     */
    public function getCurrencyService()
    {
        return $this->getCartService()
            ->getCartTotalService()
            ->getCurrencyService();
    }

    /**
     * @param $paymentService
     * @return $this
     */
    public function setPaymentService($paymentService)
    {
        $this->paymentService = $paymentService;
        return $this;
    }

    /**
     * @return PaymentService
     */
    public function getPaymentService()
    {
        return $this->paymentService;
    }

    /**
     * @param $fraudDetectionService
     * @return $this
     */
    public function setFraudDetectionService($fraudDetectionService)
    {
        $this->fraudDetectionService = $fraudDetectionService;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFraudDetectionService()
    {
        return $this->fraudDetectionService;
    }

    /**
     * @param Cart $cart
     * @return $this
     */
    public function setCart($cart)
    {
        $this->cart = $cart;
        return $this;
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param $customerToken
     * @return $this
     */
    public function setCustomerToken($customerToken)
    {
        $this->customerToken = $customerToken;
        return $this;
    }

    /**
     * @return CustomerToken
     */
    public function getCustomerToken()
    {
        return $this->customerToken;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setEnableCreatePayment($yesNo)
    {
        $this->enableEnableCreatePayment = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnableCreatePayment()
    {
        return $this->enableEnableCreatePayment;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setEnableCreateInvoice($yesNo)
    {
        $this->enableCreateInvoice = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnableCreateInvoice()
    {
        return $this->enableCreateInvoice;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setEnableCreateShipment($yesNo)
    {
        $this->enableCreateShipment = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getEnableCreateShipment()
    {
        return $this->enableCreateShipment;
    }

    /**
     * @param $paymentMethod
     * @return $this
     */
    public function setPaymentMethodCode($paymentMethod)
    {
        $this->paymentMethodCode = $paymentMethod;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentMethodCode()
    {
        return $this->paymentMethodCode;
    }

    /**
     * @param $paymentMethodService
     * @return $this
     */
    public function setPaymentMethodService($paymentMethodService)
    {
        $this->paymentMethodService = $paymentMethodService;
        return $this;
    }

    /**
     * @return PaymentMethodServiceInterface
     */
    public function getPaymentMethodService()
    {
        return $this->paymentMethodService;
    }

    /**
     * @param array $paymentData
     * @return $this
     */
    public function setPaymentData(array $paymentData)
    {
        $this->paymentData = $paymentData;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaymentData()
    {
        return $this->paymentData;
    }

    /**
     * @param $success
     * @return $this
     */
    public function setPaymentSuccess($success)
    {
        $this->paymentSuccess = $success;
        return $this;
    }

    /**
     * @return bool
     */
    public function getPaymentSuccess()
    {
        return $this->paymentSuccess;
    }

    /**
     * @param $paymentInfo
     * @return $this
     */
    public function setOrderPaymentData($paymentInfo)
    {
        $this->paymentInfo = $paymentInfo;
        return $this;
    }

    /**
     * @return ArrayWrapper
     */
    public function getOrderPaymentData()
    {
        return $this->paymentInfo;
    }

    /**
     * @param OrderPayment $payment
     * @return $this
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * @return OrderPayment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param $detectFraud
     * @return $this
     */
    public function setDetectFraud($detectFraud)
    {
        $this->detectFraud = $detectFraud;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDetectFraud()
    {
        return $this->detectFraud;
    }

    /**
     * @param $fraudRating
     * @return $this
     */
    public function setFraudRating($fraudRating)
    {
        $this->fraudRating = $fraudRating;
        return $this;
    }

    /**
     * @return int
     */
    public function getFraudRating()
    {
        return $this->fraudRating;
    }

    /**
     * @param $fraudRatingTrigger
     * @return $this
     */
    public function setFraudRatingTrigger($fraudRatingTrigger)
    {
        $this->fraudRatingTrigger = $fraudRatingTrigger;
        return $this;
    }

    /**
     * @return int
     */
    public function getFraudRatingTrigger()
    {
        return $this->fraudRatingTrigger;
    }

    /**
     * @param $isFraud
     * @return $this
     */
    public function setIsFraud($isFraud)
    {
        $this->isFraud = $isFraud;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsFraud()
    {
        return $this->isFraud;
    }

    /**
     * @param OrderInvoice $invoice
     * @return $this
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
        return $this;
    }

    /**
     * @return OrderInvoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param $isRefund
     * @return $this
     */
    public function setIsRefund($isRefund)
    {
        $this->isRefund = $isRefund;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsRefund()
    {
        return $this->isRefund;
    }

    /**
     * @param OrderRefund $refund
     * @return $this
     */
    public function setRefund($refund)
    {
        $this->refund = $refund;
        return $this;
    }

    /**
     * @return OrderRefund
     */
    public function getRefund()
    {
        return $this->refund;
    }

    /**
     * Submit Cart
     *  This is the main method used in checkout
     *
     * @throws \Exception
     * @return mixed
     */
    public function submitCart()
    {
        if ($this->getDetectFraud()) {
            $this->handleFraudDetection();
            if ($this->getIsFraud()) {
                // dont allow order to be created
                // throw new FraudulentOrderException();
            }
        }

        if ($this->getEnableCreatePayment()) {
            $this->processPayment();
        }

        $this->createOrder();

        if ($this->getEnableCreateInvoice()
            || $this->getEnableCreatePayment()
        ) {

            // create invoice
            $this->createUpdateInvoice();

            if ($this->getPaymentSuccess()) {

                $this->createOrderPayment();

                // update invoice, mark as paid
                $this->createUpdateInvoice();
            }
        }

        $event = new CoreEvent();
        $event->addData($this->getEventData())
            ->setCart($this->getCart())
            ->setOrder($this->getOrder())
            ->setCustomerToken($this->getCustomerToken())
            ->setPayment($this->getPayment())
            ->setInvoice($this->getInvoice())
            ;

        $this->getEventDispatcher()
            ->dispatch(CoreEvents::ORDER_SUBMIT_SUCCESS, $event);

        return $this;
    }

    /**
     * @return $this
     */
    public function handleFraudDetection()
    {
        $cart = $this->getCart();

        // send cart info and possibly payment info to fraud detection service

        $isFraud = false;
        $this->setIsFraud($isFraud);
        return $this;
    }

    /**
     * Capture payment via payment method service
     *
     * @throws \Exception
     */
    public function processPayment()
    {
        $cart = $this->getCart();

        $currencyService = $this->getCurrencyService();

        $baseCurrency = $this->getCurrencyService()->getBaseCurrency();
        $currency = $cart->getCurrency();
        if (!strlen($currency)) {
            $currency = $baseCurrency;
        }

        $baseGrandTotal = $cart->getTotal(GrandTotal::KEY)->getValue();

        $grandTotal = ($currency == $baseCurrency)
            ? $baseGrandTotal
            : $currencyService->convert($baseGrandTotal, $currency, $baseCurrency);

        /** @var PaymentMethodServiceInterface $paymentMethodService */
        $paymentMethodService = $this->getPaymentMethodService()
            ->setPaymentData($this->getPaymentData())
            ->setOrderData([
                'total' => $grandTotal,
                'currency' => $currency,
                'base_total' => $baseGrandTotal,
                'base_currency' => $baseCurrency,
            ]);

        switch($paymentMethodService->getAction()) {
            case PaymentMethodServiceInterface::ACTION_AUTHORIZE:
                throw new \Exception("Error with Payment Handler"); // todo : replace this with logic
                break;
            case PaymentMethodServiceInterface::ACTION_CAPTURE:

                $isCaptured = $paymentMethodService->capture()
                    ->getIsCaptured();

                if (!$isCaptured) {
                    throw new \Exception("Payment Capture Failed");
                }

                break;
            case PaymentMethodServiceInterface::ACTION_PURCHASE:

                $isCaptured = $paymentMethodService->purchase()
                    ->getIsPurchased();

                if (!$isCaptured) {
                    throw new \Exception("Payment Failed");
                }

                break;
            case PaymentMethodServiceInterface::ACTION_CREATE_TOKEN:

                $isTokenCreated = $paymentMethodService->createToken()
                    ->getIsTokenCreated();

                if (!$isTokenCreated) {
                    throw new \Exception("Payment Token Failed");
                }

                $customerId = $this->getCart()->getCustomer()->getId();
                $paymentData = $this->getPaymentData();
                $customerTokenData = $paymentMethodService->extractCustomerTokenData();

                $customer = null;
                $customerToken = null;

                if ($customerId) {

                    $customer = $this->getEntityService()->find(EntityConstants::CUSTOMER, $customerId);
                    $customerToken = $this->getEntityService()->findOneBy(EntityConstants::CUSTOMER_TOKEN, [
                        'customer' => $customerId,
                        'token' => $paymentData['token'],
                    ]);
                }

                if (!$customerToken) {

                    $customerToken = $this->getEntityService()->getInstance(EntityConstants::CUSTOMER_TOKEN);
                    $customerToken->fromArray($customerTokenData);

                    if ($customerId) {

                        $customerToken->setCustomer($customer);
                        $this->getEntityService()->persist($customerToken);
                    }
                }

                $this->setCustomerToken($customerToken);

                $paymentMethodService->setPaymentCustomerToken($customerToken);

                $isPurchasedStoredToken = $paymentMethodService->purchaseStoredToken()
                    ->getIsPurchasedStoredToken();

                if (!$isPurchasedStoredToken) {
                    throw new \Exception("Stored Token Payment Failed");
                }

                break;
            case PaymentMethodServiceInterface::ACTION_PURCHASE_STORED_TOKEN:

                $customerId = $this->getCart()->getCustomer()->getId();

                if (!$customerId) {
                    throw new \Exception("Stored Token Payment Failed");
                }

                $paymentData = $this->getPaymentData();
                $token = isset($paymentData['token'])
                    ? $paymentData['token']
                    : '';

                if (!$token) {
                    throw new \Exception("Stored Token Payment Failed");
                }

                $customerToken = $this->getEntityService()->findOneBy(EntityConstants::CUSTOMER_TOKEN, [
                    'customer' => $customerId,
                    'token' => $token,
                ]);

                if (!$customerToken) {
                    throw new \Exception("Stored Token Payment Failed");
                }

                $this->setCustomerToken($customerToken);

                $paymentMethodService->setPaymentCustomerToken($customerToken);

                $isPurchasedStoredToken = $paymentMethodService->purchaseStoredToken()
                    ->getIsPurchasedStoredToken();

                if (!$isPurchasedStoredToken) {
                    throw new \Exception("Stored Token Payment Failed");
                }

                break;
            case PaymentMethodServiceInterface::ACTION_PURCHASE_AND_SUBSCRIBE_RECURRING:

                $isTokenCreated = $paymentMethodService->createToken()
                    ->getIsTokenCreated();

                if (!$isTokenCreated) {
                    throw new \Exception("Payment Token Failed");
                }

                $customerId = $this->getCart()->getCustomer()->getId();
                $paymentData = $this->getPaymentData();
                $customerTokenData = $paymentMethodService->extractCustomerTokenData();

                $customer = null;
                $customerToken = null;
                if ($customerId) {

                    $customer = $this->getEntityService()->find(EntityConstants::CUSTOMER, $customerId);
                    $customerToken = $this->getEntityService()->findOneBy(EntityConstants::CUSTOMER_TOKEN, [
                        'customer' => $customerId,
                        'token' => $paymentData['token'],
                    ]);
                }

                if (!$customerToken) {

                    $customerToken = $this->getEntityService()->getInstance(EntityConstants::CUSTOMER_TOKEN);
                    $customerToken->fromArray($customerTokenData);

                    if ($customerId) {
                        $customerToken->setCustomer($customer);
                        $this->getEntityService()->persist($customerToken);
                    }
                }

                $this->setCustomerToken($customerToken);

                $paymentMethodService->setPaymentCustomerToken($customerToken);

                $isSubscribed = $paymentMethodService->purchaseAndSubscribeRecurring()
                    ->getIsPurchasedAndSubscribedRecurring();

                if (!$isSubscribed) {
                    throw new \Exception("Subscription Failed");
                }

                break;
            case PaymentMethodServiceInterface::ACTION_AUTHORIZE_REDIRECT:
                throw new \Exception("Error with Payment Handler"); // todo : replace this with logic
                break;
            case PaymentMethodServiceInterface::ACTION_PURCHASE_CALLBACK:
                throw new \Exception("Error with Payment Handler"); // todo : replace this with logic
                break;
            default:
                throw new \Exception("Error with Payment Configuration");
                break;
        }

        $this->setPaymentSuccess(1);

        return $this;
    }

    /**
     * @return Order
     * @throws \InvalidArgumentException
     */
    public function createOrder()
    {
        $cart = $this->getCart();
        if (!$cart) {
            throw new \InvalidArgumentException("Cart is Invalid");
        }

        // Not allowing a blind payment without totals being collected first
        if (!$cart->getTotals()) {
            throw new \InvalidArgumentException("Cart is Invalid. Does not contain totals");
        }

        $baseCurrency = $this->getCurrencyService()->getBaseCurrency();
        $currency = $cart->getCurrency();
        if (!strlen($currency)) {
            $currency = $baseCurrency;
        }

        $cartCustomer = $cart->getCustomer();

        $order = $this->getOrder();
        if (!$order) {

            $order = $this->getEntityService()->getInstance(EntityConstants::ORDER);

            $customer = $this->getEntityService()->find(EntityConstants::CUSTOMER, $cartCustomer->getId());
            if (!$customer) {
                throw new \InvalidArgumentException("Customer account is required");
            }

            $order->setCustomer($customer)
                ->setEmail($cartCustomer->getEmail())
                ->setBillingName($cartCustomer->getBillingName())
                ->setBillingPhone($cartCustomer->getBillingPhone())
                ->setBillingStreet($cartCustomer->getBillingStreet())
                ->setBillingCity($cartCustomer->getBillingCity())
                ->setBillingRegion($cartCustomer->getBillingRegion())
                ->setBillingPostcode($cartCustomer->getBillingPostcode())
                ->setBillingCountryId($cartCustomer->getBillingCountryId())
                ->setShippingName($cartCustomer->getShippingName())
                ->setShippingPhone($cartCustomer->getShippingPhone())
                ->setShippingStreet($cartCustomer->getShippingStreet())
                ->setShippingCity($cartCustomer->getShippingCity())
                ->setShippingRegion($cartCustomer->getShippingRegion())
                ->setShippingPostcode($cartCustomer->getShippingPostcode())
                ->setShippingCountryId($cartCustomer->getShippingCountryId())
                ;
        }

        $order->setJson($cart->toJson());

        // Totals
        $baseDiscountTotal = $cart->getTotal(DiscountTotal::KEY)->getValue();
        $baseGrandTotal = $cart->getTotal(GrandTotal::KEY)->getValue();
        $baseItemTotal = $cart->getTotal(ItemTotal::KEY)->getValue();
        $baseShipmentTotal = $cart->getTotal(ShipmentTotal::KEY)
            ? $cart->getTotal(ShipmentTotal::KEY)->getValue()
            : '0.00';

        $baseTaxTotal = $cart->getTotal(TaxTotal::KEY)->getValue();

        $order->setBaseCurrency($baseCurrency)
            ->setBaseTotal($baseGrandTotal)
            ->setBaseItemTotal($baseItemTotal)
            ->setBaseTaxTotal($baseTaxTotal)
            ->setBaseShippingTotal($baseShipmentTotal)
            ->setBaseDiscountTotal($baseDiscountTotal);

        if ($currency == $baseCurrency) {

            $order->setCurrency($baseCurrency)
                ->setTotal($baseGrandTotal)
                ->setItemTotal($baseItemTotal)
                ->setTaxTotal($baseTaxTotal)
                ->setShippingTotal($baseShipmentTotal)
                ->setDiscountTotal($baseDiscountTotal);

        } else {

            $currencyService = $this->getCurrencyService();
            $grandTotal = $currencyService->convert($baseGrandTotal, $currency, $baseCurrency);
            $itemTotal = $currencyService->convert($baseItemTotal, $currency, $baseCurrency);
            $taxTotal = $currencyService->convert($baseTaxTotal, $currency, $baseCurrency);
            $shipmentTotal = $currencyService->convert($baseShipmentTotal, $currency, $baseCurrency);
            $discountTotal = $currencyService->convert($baseDiscountTotal, $currency, $baseCurrency);

            $order->setCurrency($currency)
                ->setTotal($grandTotal)
                ->setItemTotal($itemTotal)
                ->setTaxTotal($taxTotal)
                ->setShippingTotal($shipmentTotal)
                ->setDiscountTotal($discountTotal);

        }

        $baseReferenceNbr = '100000000';
        $order->setReferenceNbr($baseReferenceNbr);
        $order->setCreatedAt(new \DateTime('now'));

        // save order
        $this->getEntityService()->persist($order);

        $orderId = $order->getId();
        $referenceNbr = ((int) $baseReferenceNbr) + $orderId;
        $order->setReferenceNbr($referenceNbr);
        // update order
        $this->getEntityService()->persist($order);

        // handle EAV, if necessary
        //if ($formData /* && $this->getEntityService()->isEAV() */) {
        //    $this->getEntityService()
        //        ->handleVarValueCreate(EntityConstants::ORDER, $order, $formData);
        //}

        // set order for further processing
        $this->setOrder($order);

        // save items
        $this->createOrderItems();

        // todo: save shipments
        //$this->createOrderShipment();

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function createOrderItems()
    {
        if (!$this->getOrder()) {
            throw new \Exception("Cannot create Order Items. Order is not set");
        }

        if (!$this->getCart()) {
            throw new \Exception("Cannot create Order Items. Cart is not set");
        }

        $currencyService = $this->getCurrencyService();
        $baseCurrency = $this->getCurrencyService()->getBaseCurrency();
        $currency = $this->getCart()->getCurrency();
        if (!strlen($currency)) {
            $currency = $baseCurrency;
        }

        if ($this->getCart()->hasItems()) {
            foreach($this->getCart()->getItems() as $item) {

                $data = $item->getData();
                if (isset($data['id'])) {
                    unset($data['id']);
                }

                $orderItem = $this->getEntityService()->getInstance(EntityConstants::ORDER_ITEM);
                $orderItem->fromArray($data);
                $orderItem->setOrder($this->getOrder());
                $orderItem->setJson(json_encode($item));

                // handle currency

                $orderItem->setBasePrice($item->getPrice());
                $orderItem->setBaseCost($item->getCost());
                $orderItem->setBaseCurrency($baseCurrency);

                if ($currency == $baseCurrency) {
                    $orderItem->setPrice($orderItem->getBasePrice());
                    $orderItem->setCost($orderItem->getBaseCost());
                    $orderItem->setCurrency($orderItem->getBaseCurrency());
                } else {
                    $orderItem->setCurrency($currency);
                    $orderItem->setPrice($currencyService->convert($orderItem->getPrice(), $currency, $baseCurrency));
                    $orderItem->setCost($currencyService->convert($orderItem->getCost(), $currency, $baseCurrency));
                }

                $this->getEntityService()->persist($orderItem);
            }
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function createOrderShipment()
    {
        if (!$this->getOrder()) {
            throw new \Exception("Cannot create Order Shipments. Order is not set");
        }

        if (!$this->getCart()) {
            throw new \Exception("Cannot create Order Items. Cart is not set");
        }

        $currencyService = $this->getCurrencyService();
        $baseCurrency = $this->getCurrencyService()->getBaseCurrency();
        $currency = $this->getCart()->getCurrency();
        if (!strlen($currency)) {
            $currency = $baseCurrency;
        }

        if ($this->getCart()->hasShipments()) {
            foreach($this->getCart()->getShipments() as $shipment) {

                $data = $shipment->getData();

                // careful with ID handling
                if (isset($data['id'])) {
                    unset($data['id']);
                }

                if (!isset($data['cost']) || !is_numeric($data['cost'])) {
                    $shipment->setCost('0.00');
                }

                $orderShipment = $this->getEntityService()->getInstance(EntityConstants::ORDER_SHIPMENT);
                $orderShipment->fromArray($data);
                $orderShipment->setOrder($this->getOrder());

                $orderShipment->setBasePrice($shipment->getPrice());
                $orderShipment->setBaseCost($shipment->getCost());
                $orderShipment->setBaseCurrency($baseCurrency);

                if ($currency == $baseCurrency) {
                    $orderShipment->setPrice($shipment->getPrice());
                    $orderShipment->setCost($shipment->getCost());
                    $orderShipment->setCurrency($shipment->getCurrency());
                } else {
                    $orderShipment->setCurrency($currency);
                    $orderShipment->setPrice($currencyService->convert($shipment->getPrice(), $currency, $baseCurrency));
                    $orderShipment->setCost($currencyService->convert($shipment->getCost(), $currency, $baseCurrency));
                }

                $orderShipment->setCreatedAt(new \DateTime('now'));

                $this->getEntityService()->persist($orderShipment);
            }
        }

        return $this;
    }

    /**
     * Save successful payment, and associate it to the order and invoice
     *
     * @return $this
     * @throws \Exception
     */
    public function createOrderPayment()
    {
        $order = $this->getOrder();
        if (!$this->getOrder()) {
            throw new \Exception("Cannot create Order Payments. Order is not set");
        }

        $paymentData = $this->getPaymentMethodService()->extractOrderPaymentData();
        if (!$paymentData) {
            throw new \InvalidArgumentException("Payment Info is Invalid");
        }

        $orderPayment = $this->getEntityService()->getInstance(EntityConstants::ORDER_PAYMENT);

        // careful with IDs
        if (isset($paymentData['id'])) {
            unset($paymentData['id']);
        }

        $orderPayment->fromArray($paymentData);
        $orderPayment->setOrder($order);

        if ($this->getInvoice()) {
            $orderPayment->setInvoice($this->getInvoice());
        }

        $orderPayment->setCreatedAt(new \DateTime('now'));

        $this->getEntityService()->persist($orderPayment);
        $this->setPayment($orderPayment);

        return $this;
    }

    /**
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function createUpdateInvoice()
    {
        $order = $this->getOrder();
        if (!$order) {
            throw new \InvalidArgumentException("Order is Invalid");
        }

        $baseAmountPaid = $this->getPayment()
            ? $this->getPayment()->getBaseAmount()
            : '0.00';

        $amountPaid = $this->getPayment()
            ? $this->getPayment()->getAmount()
            : '0.00';

        $invoice = $this->getInvoice()
            ? $this->getInvoice()
            : $this->getEntityService()->getInstance(EntityConstants::ORDER_INVOICE);

        $invoice->setOrder($order)
            ->setBaseCurrency($order->getBaseCurrency())
            ->setBaseAmountDue($order->getBaseTotal())
            ->setBaseAmountPaid($baseAmountPaid)
            ->setCurrency($order->getCurrency())
            ->setAmountDue($order->getTotal())
            ->setAmountPaid($amountPaid);

        if (!$invoice->getId()) {
            $invoice->setCreatedAt(new \DateTime('now'));
        }

        $this->getEntityService()->persist($invoice);

        $this->setInvoice($invoice);

        return $this;
    }

    /**
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function createRefund()
    {
        $order = $this->getOrder();
        if (!$order) {
            throw new \InvalidArgumentException("Order is Invalid");
        }

        // $this->setRefund();

        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function payRefund()
    {
        $refund = $this->getRefund();
        if (!$refund) {
            throw new \InvalidArgumentException("Refund is Invalid.");
        }

        // process payment via Omnipay

        // if successful, create OrderPayment via Entity Service

        // else throw PaymentFailedException
        return $this;
    }
}
