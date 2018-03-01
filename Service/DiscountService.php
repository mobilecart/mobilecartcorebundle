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

use MobileCart\CoreBundle\CartComponent\Cart;
use MobileCart\CoreBundle\CartComponent\Discount;
use MobileCart\CoreBundle\CartComponent\RuleConditionCompare;
use MobileCart\CoreBundle\Constants\EntityConstants;

class DiscountService
{
    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var bool
     */
    protected $isDiscountEnabled = false;

    /**
     * @var \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    protected $entityService;

    /**
     * @param Cart $cart
     * @return $this
     */
    public function setCart(Cart $cart)
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
     * @param $isEnabled
     * @return $this
     */
    public function setIsDiscountEnabled($isEnabled)
    {
        $this->isDiscountEnabled = $isEnabled;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDiscountEnabled()
    {
        return $this->isDiscountEnabled;
    }

    /**
     * @param \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface $entityService
     * @return $this
     */
    public function setEntityService(\MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface $entityService)
    {
        $this->entityService = $entityService;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Service\RelationalDbEntityServiceInterface
     */
    public function getEntityService()
    {
        return $this->entityService;
    }

    /**
     * @param bool $asComponent
     * @return array
     */
    public function getAutoDiscounts($asComponent = false)
    {
        $filters = [
            'is_auto' => 1,
            //'start_date' => '', // todo:
            //'end_date' => '', // todo:
        ];

        $entities = $this->getEntityService()
            ->findBy(EntityConstants::DISCOUNT, $filters);

        if (!$asComponent) {
            return $entities;
        }

        if (!$entities) {
            return [];
        }

        $discounts = [];
        foreach($entities as $entity) {

            $discount = new Discount();

            $discount->fromArray($entity->getData())
                ->setAppliedAs($entity->getAppliedAs())
                ->setAppliedTo($entity->getAppliedTo());

            $preCondition = new RuleConditionCompare();
            $preCondition->fromJson($entity->getPreConditions());

            $targetCondition = new RuleConditionCompare();
            $targetCondition->fromJson($entity->getTargetConditions());

            $discount->setPreConditionCompare($preCondition);
            $discount->setTargetConditionCompare($targetCondition);

            $discounts[] = $discount;
        }

        return $discounts;
    }

    //todo: getCartDiscounts($asComponents = false){ }

    /**
     * @param $id
     * @return \MobileCart\CoreBundle\Entity\Discount
     */
    public function find($id)
    {
        return $this->getEntityService()->find(EntityConstants::DISCOUNT, $id);
    }

    /**
     * @param $code
     * @return \MobileCart\CoreBundle\Entity\Discount
     */
    public function findByCouponCode($code)
    {
        return $this->getEntityService()->findBy(EntityConstants::DISCOUNT, [
            'coupon_code' => $code,
        ]);
    }
}
