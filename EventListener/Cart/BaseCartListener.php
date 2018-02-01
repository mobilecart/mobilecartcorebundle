<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class BaseCartListener
 * @package MobileCart\CoreBundle\EventListener\Cart
 */
class BaseCartListener
{
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
     * @return \MobileCart\CoreBundle\Service\AbstractEntityService
     */
    public function getEntityService()
    {
        return $this->getCartService()->getEntityService();
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return $this
     */
    public function initApiRequest(\Symfony\Component\HttpFoundation\Request $request)
    {
        $key = 'cart_id';
        if (!$this->getCartService()->getCartEntity()
            && $request->get($key, '')
        ) {
            $hash = $request->get($key);

            // admin users have full access
            if ($this->getCartService()->getIsAdminUser()) {
                $cartEntity = $this->getEntityService()->findOneBy(EntityConstants::CART, [
                    'hash_key' => $hash
                ]);
                if ($cartEntity) {
                    $this->getCartService()->setCartEntity($cartEntity);
                }
            } else {
                // customers need to
                if ($this->getCartService()->getCustomerEntity()) {
                    $cartEntity = $this->getEntityService()->findOneBy(EntityConstants::CART, [
                        'hash_key' => $hash,
                        'customer' => $this->getCartService()->getCustomerEntity()->getId()
                    ]);
                    if ($cartEntity) {
                        $this->getCartService()->setCartEntity($cartEntity);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \MobileCart\CoreBundle\CartComponent\Cart
     */
    public function initCart(\Symfony\Component\HttpFoundation\Request $request)
    {
        $this->initApiRequest($request);
        return $this->getCartService()->getCart();
    }
}
