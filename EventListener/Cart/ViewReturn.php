<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\HttpFoundation\JsonResponse;
use MobileCart\CoreBundle\Event\CoreEvent;

/**
 * Class ViewReturn
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class ViewReturn
{
    /**
     * @var \MobileCart\CoreBundle\Service\ThemeService
     */
    protected $themeService;

    /**
     * @var \MobileCart\CoreBundle\Service\CartService
     */
    protected $cartService;

    /**
     * @param \MobileCart\CoreBundle\Service\CartService $cartService
     * @return $this
     */
    public function setCartService(\MobileCart\CoreBundle\Service\CartService $cartService)
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
     * @param $themeService
     * @return $this
     */
    public function setThemeService($themeService)
    {
        $this->themeService = $themeService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\ThemeService
     */
    public function getThemeService()
    {
        return $this->themeService;
    }

    /**
     * @param CoreEvent $event
     */
    public function onCartViewReturn(CoreEvent $event)
    {
        $event->setSuccess(true);
        $event->setReturnData('cart', $this->getCartService()->getCart());

        if ($event->isJsonResponse()) {
            $event->setResponse(new JsonResponse($event->getReturnData()));
        } else {

            switch($event->getRequest()->get('ajax', '')) {
                case '1':

                    $event->setResponse($this->getThemeService()->renderFrontend(
                        'Cart:partial.html.twig',
                        $event->getReturnData()
                    ));

                    break;
                case 'mini':

                    $event->setResponse($this->getThemeService()->renderFrontend(
                        'Cart:mini.html.twig',
                        $event->getReturnData()
                    ));

                    break;
                case 'confirm':

                    $event->setResponse($this->getThemeService()->renderFrontend(
                        'Cart:confirm_modal.html.twig',
                        $event->getReturnData()
                    ));

                    break;
                default:

                    $event->setResponse($this->getThemeService()->renderFrontend(
                        'Cart:index.html.twig',
                        $event->getReturnData()
                    ));

                    break;
            }
        }
    }
}
