<?php

namespace MobileCart\CoreBundle\EventListener\Security;

use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\HttpUtils;

class Logout implements LogoutSuccessHandlerInterface
{
    protected $httpUtils;
    protected $targetUrl;
    protected $cartSessionService;

    /**
     * @param HttpUtils $httpUtils
     * @param string    $targetUrl
     */
    public function __construct(HttpUtils $httpUtils, $targetUrl = '/')
    {
        $this->httpUtils = $httpUtils;

        $this->targetUrl = $targetUrl;
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
     * @return mixed
     */
    public function getCartSessionService()
    {
        return $this->cartSessionService;
    }

    /**
     * {@inheritdoc}
     */
    public function onLogoutSuccess(Request $request)
    {
        $customer = $this->getCartSessionService()->getCustomerInstance();
        $this->getCartSessionService()
            ->setCustomer($customer)
            ->collectShippingMethods()
            ->collectTotals();

        return $this->httpUtils->createRedirectResponse($request, $this->targetUrl);
    }
}
