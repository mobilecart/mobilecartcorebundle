<?php

namespace MobileCart\CoreBundle\EventListener\Cart;

use Symfony\Component\EventDispatcher\Event;
use MobileCart\CoreBundle\CartComponent\Total;

class DiscountTotal extends Total
{
    const KEY = 'discounts';
    const LABEL = 'Discount';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @var array
     */
    protected $discounts;

    /**
     * @var mixed
     */
    protected $discountService;

    protected $event;

    protected function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    protected function getEvent()
    {
        return $this->event;
    }

    public function getReturnData()
    {
        return $this->getEvent()->getReturnData()
            ? $this->getEvent()->getReturnData()
            : [];
    }

    /**
     * @param $discounts
     * @return $this
     */
    public function setDiscounts(array $discounts)
    {
        $this->discounts = $discounts;
        return $this;
    }

    /**
     * @return array
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }

    /**
     * @param $discountService
     * @return $this
     */
    public function setDiscountService($discountService)
    {
        $this->discountService = $discountService;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiscountService()
    {
        return $this->discountService;
    }

    public function onCartTotalCollect(Event $event)
    {
        // this includes both pre-tax and post-tax discounts
        //  if needed, this class can be split into 2 Totals
        //  before and after the tax total
        //  call $cart->getCalculator():
        // getPreTaxDiscountTotal() and getPostTaxDiscountTotal()

        $this->setEvent($event);
        $returnData = $this->getReturnData();

        $cart = $event->getCart();

        if ($event->getApplyAutoDiscounts()) {

            $asComponent = true;
            $discounts = $this->getDiscountService()
                ->setCart($cart)
                ->getAutoDiscounts($asComponent);

            if ($discounts) {
                foreach($discounts as $discount) {
                    $discount->reapplyIfValid($cart);
                }
            }
        }

        $discountTotal = $cart->getCalculator()
            ->getDiscountTotal();

        $discounts = is_array($cart->getDiscounts())
            ? $cart->getDiscounts()
            : [];

        $this->setDiscounts($discounts);

        $event->setCart($cart);

        $this->setKey(self::KEY)
            ->setLabel(self::LABEL)
            ->setValue($discountTotal)
            ->setIsAdd(0); // subtract

        $event->addTotal($this);

        $event->setReturnData($returnData);
    }
}
