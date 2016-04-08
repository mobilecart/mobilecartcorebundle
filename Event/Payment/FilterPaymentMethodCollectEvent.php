<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Event\Payment;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\Payment\CollectPaymentMethodRequest;
use MobileCart\CoreBundle\CartComponent\ArrayWrapper;

class FilterPaymentMethodCollectEvent extends Event
{
    /**
     * @var array
     */
    protected $methods = []; // r[method] = ArrayWrapper object

    /**
     * @var bool
     */
    protected $handlePayment = false;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var bool
     */
    protected $findService = false;

    /**
     * @var
     */
    protected $service;

    /**
     * @var CollectPaymentMethodRequest $methodRequest
     */
    protected $methodRequest;

    /**
     * @var array
     */
    protected $paymentData = [];

    /**
     * @var array
     */
    protected $orderData = [];

    /**
     * @var object
     */
    protected $response;

    /**
     * Add Payment Method
     *
     * @param ArrayWrapper $method
     * @return $this
     */
    public function addMethod(ArrayWrapper $method)
    {
        $code = $method->get('code');
        $this->methods[$code] = $method;
        return $this;
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param $handle
     * @return $this
     */
    public function setHandlePayment($handle)
    {
        $this->handlePayment = (bool) $handle;
        return $this;
    }

    /**
     * @return bool
     */
    public function getHandlePayment()
    {
        return $this->handlePayment;
    }

    /**
     * @param $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setFindService($yesNo)
    {
        $this->findService = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getFindService()
    {
        return $this->findService;
    }

    public function setService($service)
    {
        $this->service = $service;
        return $this;
    }

    public function getService()
    {
        return $this->service;
    }

    /**
     * @param CollectPaymentMethodRequest $request
     * @return $this
     */
    public function setCollectPaymentMethodRequest(CollectPaymentMethodRequest $request)
    {
        $this->methodRequest = $request;
        return $this;
    }

    /**
     * @return CollectPaymentMethodRequest
     */
    public function getCollectPaymentMethodRequest()
    {
        return $this->methodRequest;
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
     * @return array
     */
    public function getPaymentData()
    {
        return $this->paymentData;
    }

    /**
     * @param array $orderData
     * @return $this
     */
    public function setOrderData(array $orderData)
    {
        $this->orderData = $orderData;
        return $this;
    }

    /**
     * @return array
     */
    public function getOrderData()
    {
        return $this->orderData;
    }

    /**
     * @param $response
     * @return mixed
     */
    public function setResponse($response)
    {
        $this->response = $response;
        return $this->response;
    }

    /**
     * @return object
     */
    public function getResponse()
    {
        return $this->response;
    }
}
