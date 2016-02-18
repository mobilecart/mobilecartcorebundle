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
use MobileCart\CoreBundle\CartComponent\ArrayWrapper;

class FilterPaymentServiceEvent extends Event
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
     * @var object
     */
    protected $request;

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
     * @param $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return object
     */
    public function getRequest()
    {
        return $this->request;
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
