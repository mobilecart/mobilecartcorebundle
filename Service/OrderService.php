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

use MobileCart\CoreBundle\EventListener\Cart\DiscountTotal;
use MobileCart\CoreBundle\EventListener\Cart\GrandTotal;
use MobileCart\CoreBundle\EventListener\Cart\ItemTotal;
use MobileCart\CoreBundle\EventListener\Cart\ShipmentTotal;
use MobileCart\CoreBundle\EventListener\Cart\TaxTotal;
use MobileCart\CoreBundle\Payment\Exception\PaymentFailedException;

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
     * @var PaymentMethodServiceInterface
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
     * @var \MobileCart\CoreBundle\Payment\Payment
     */
    protected $paymentInfo;

    /**
     * @var OrderPayment
     */
    protected $payment;

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
     * @param $paymentInfo
     * @return $this
     */
    public function setPaymentInfo($paymentInfo)
    {
        $this->paymentInfo = $paymentInfo;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Payment\Payment
     */
    public function getPaymentInfo()
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
    public function setDetectFraudBeforeOrder($detectFraud)
    {
        $this->detectFraud = $detectFraud;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDetectFraudBeforeOrder()
    {
        return $this->detectFraud;
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
     * @return mixed
     */
    public function submitCart()
    {
        if ($this->getDetectFraudBeforeOrder()) {
            $this->handleFraudDetection();
            if ($this->getIsFraud()) {
                // throw new FraudulentOrderException();
            }
        }

        $cart = $this->getCart();

        /*
        Scenarios :

        A. Shipping enabled , capture on shipment
         1. authorize
         2. create order, invoice, email customer
         3. capture, mark invoice as paid, create shipment
         4. enter tracking number, email customer

        B. Shipping enabled, capture on invoice
         1. authorize, capture
         2. create order, invoice
         3. mark invoice as paid, email customer
         4. create shipment, enter tracking number, email customer

        C. Shipping disabled, capture on invoice
         1. authorize, capture
         2. create order, invoice, mark invoice as paid
         3. post-handling for digital downloads, subscription, etc
         4. email customer

        //*/

        if ($this->getEnableCreatePayment()) {

            /** @var PaymentMethodServiceInterface $paymentMethodService */
            $paymentMethodService = $this->getPaymentMethodService()
                ->setPaymentData($this->getPaymentData());

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

            $paymentMethodService->setOrderData([
                'total' => $grandTotal,
                'currency' => $currency,
            ]);

            if ($paymentMethodService->canAuthorize()
                && $paymentMethodService->getEnableAuthorize()) {

                $isAuthorized = $paymentMethodService->authorize()
                    ->getIsAuthorized();

                if (!$isAuthorized) {
                    throw new \Exception("Payment Authorization Failed"); // todo : different exception
                }
            }

            if ($paymentMethodService->getEnableCaptureOnInvoice()) {

                $isCaptured = $paymentMethodService->capture()
                    ->getIsCaptured();

                if (!$isCaptured) {
                    throw new \Exception("Payment Authorization Failed"); // todo : different exception
                }
            }

        }

        $this->createOrder();

        if ($this->getEnableCreateInvoice()) {
            $this->createInvoice();
            //$this->payInvoice();
        }

        // todo : if paymentSuccess create OrderPayment object

        return $this;
    }

    /**
     *
     */
    public function handleFraudDetection()
    {

        $order = $this->getOrder();
        $invoice = $this->getInvoice();

        // todo : figure out where we are. is order created? is invoice created ?

        if ($invoice) {
            // todo : was the order already checked for fraud ?
            // todo : call fraud detection service, set isFraud as necessary

        } elseif ($order) {
            // todo : was the order already checked for fraud ?
            // todo : call fraud detection service, set isFraud as necessary

        } else {
            // todo : call fraud detection service, set isFraud as necessary
        }


        // todo : update info as necessary
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

            // todo : phone number !

            $order->setCustomer($customer)
                ->setEmail($cartCustomer->getEmail())
                ->setBillingName($cartCustomer->getBillingName())
                ->setBillingStreet($cartCustomer->getBillingStreet())
                ->setBillingCity($cartCustomer->getBillingCity())
                ->setBillingRegion($cartCustomer->getBillingRegion())
                ->setBillingPostcode($cartCustomer->getBillingPostcode())
                ->setBillingCountryId($cartCustomer->getBillingCountryId())
                ->setShippingName($cartCustomer->getShippingName())
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
        $baseShipmentTotal = $cart->getTotal(ShipmentTotal::KEY)->getValue();
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

        // save shipments
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

        //$invoice = $this->getInvoice();
        //if (!$invoice) {
        //    throw new \InvalidArgumentException("Invoice is Invalid");
        //}

        $paymentInfo = $this->getPaymentInfo();
        if (!$paymentInfo) {
            throw new \InvalidArgumentException("Payment Info is Invalid");
        }

        $orderPayment = $this->getEntityService()->getInstance(EntityConstants::ORDER_PAYMENT);
        $paymentData = $paymentInfo->getData();

        // careful with IDs
        if (isset($paymentData['id'])) {
            unset($paymentData['id']);
        }

        $orderPayment->fromArray($paymentData);
        $orderPayment->setOrder($order);
        //$orderPayment->setInvoice($invoice);

        $this->getEntityService()->persist($orderPayment);
        $this->setPayment($orderPayment);

        return $this;
    }

    /**
     * @return OrderInvoice
     * @throws \InvalidArgumentException
     */
    public function createInvoice()
    {
        $order = $this->getOrder();
        if (!$order) {
            throw new \InvalidArgumentException("Order is Invalid");
        }

        $invoice = $this->getEntityService()->getInstance(EntityConstants::ORDER_INVOICE);
        $invoice->setOrder($order)
            ->setBaseCurrency($order->getBaseCurrency())
            ->setBaseAmountDue($order->getBaseTotal())
            ->setBaseAmountPaid('0.00')
            ->setCurrency($order->getCurrency())
            ->setAmountDue($order->getTotal())
            ->setAmountPaid('0.00');

        $this->getEntityService()->persist($invoice);

        $this->setInvoice($invoice);

        return $this;
    }

    /**
     * @return $this
     * @throws \MobileCart\CoreBundle\Payment\Exception\PaymentFailedException
     * @throws \InvalidArgumentException
     */
    public function payInvoice()
    {
        $order = $this->getOrder();
        if (!$order) {
            throw new \InvalidArgumentException("Order is Invalid");
        }

        $invoice = $this->getInvoice();
        if (!$invoice) {
            throw new \InvalidArgumentException("Invoice is Invalid");
        }

        if ($this->getDetectFraud()) {
            $this->handleFraudDetection();
            if ($this->getIsFraud()) {
                // throw new FraudulentOrderException();
            }
        }

        // todo : store row in payment log; preferably a CSV or db table

        // process payment via PaymentService
        /** @var \MobileCart\CoreBundle\Payment\ServiceResponse $response */
        $response = $this->getPaymentService()
            ->handlePayment($this->getPaymentMethod(), $this->getPaymentData(), $order->getData());

        if ($response->getSuccess()) {

            $paymentInfo = $response->getPayment();
            // set Payment Info using a formal object
            $this->setPaymentInfo($paymentInfo);
            // store payment history
            $this->createOrderPayment();
            // update invoice

            $isPaid = ($paymentInfo->getBaseAmount() >= $this->getInvoice()->getBaseAmountDue())
                ? 1
                : 0;

            $this->getInvoice()
                ->setBaseCurrency($paymentInfo->getBaseCurrency())
                ->setCurrency($paymentInfo->getCurrency())
                ->setBaseAmountPaid($paymentInfo->getBaseAmount())
                ->setAmountPaid($paymentInfo->getAmount())
                ->setIsPaid($isPaid);

            $invoice = $this->getInvoice();
            $this->getEntityService()->persist($invoice);
            $this->setInvoice($invoice);

        } else {
            throw new PaymentFailedException("Payment failed.");
        }

        return $this;
    }

    /**
     * @return OrderRefund
     * @throws \InvalidArgumentException
     */
    public function createRefund()
    {
        $order = $this->getOrder();
        if (!$order) {
            throw new \InvalidArgumentException("Order is Invalid");
        }

        return $this->refund;
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
    }
}
