<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Shipping;

use MobileCart\CoreBundle\CartComponent\ArrayWrapper;

/**
 * Class Rate
 * @package MobileCart\CoreBundle\Shipping
 */
class Rate extends ArrayWrapper
{
    const ID = 'id';
    const CURRENCY = 'currency';
    const PRICE = 'price';
    const BASE_PRICE = 'base_price';
    const COST = 'cost';
    const HANDLING_COST = 'handling_cost';
    const COMPANY = 'company';
    const METHOD = 'method';
    const TITLE = 'title';
    const SORT = 'sort';
    const PRODUCT_IDS = 'product_ids';
    const SKUS = 'skus';

    public function __construct()
    {
        parent::__construct($this->getDefaults());
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return [
            self::ID            => '',
            self::CURRENCY      => 'USD',
            self::PRICE         => '',
            self::BASE_PRICE    => '',
            self::COST          => '',
            self::HANDLING_COST => '',
            self::COMPANY       => '',
            self::METHOD        => '',
            self::TITLE         => '',
            self::SORT          => 1,
            self::PRODUCT_IDS   => [],
            self::SKUS          => [],
        ];
    }

    public function fromArray(array $data)
    {
        $this->addData($this->getDefaults());
        if ($data) {
            foreach($data as $key => $value) {
                switch($key) {
                    case self::ID:
                        $this->setId($value);
                        break;
                    case self::CURRENCY:
                        $this->setCurrency($value);
                        break;
                    case self::PRICE:
                        $this->setPrice($value);
                        break;
                    case self::BASE_PRICE:
                        $this->setBasePrice($value);
                        break;
                    case self::COST:
                        $this->setCost($value);
                        break;
                    case self::HANDLING_COST:
                        $this->setHandlingCost($value);
                        break;
                    case self::COMPANY:
                        $this->setCompany($value);
                        break;
                    case self::METHOD:
                        $this->setMethod($value);
                        break;
                    case self::TITLE:
                        $this->setTitle($value);
                        break;
                    case self::SORT:
                        $this->setSort($value);
                        break;
                    case self::PRODUCT_IDS:
                        $this->setProductIds($value);
                        break;
                    case self::SKUS:
                        $this->setSkus($value);
                        break;
                    default:

                        break;
                }
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->getCompany() . '_' . $this->getMethod();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = parent::toArray();
        $data['code'] = $this->getCode();
        return $data;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->data[self::ID] = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->data[self::ID];
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->data[self::CURRENCY] = $currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->data[self::CURRENCY];
    }

    /**
     * @param $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->data[self::PRICE] = $price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->data[self::PRICE];
    }

    /**
     * @param $price
     * @return $this
     */
    public function setBasePrice($price)
    {
        $this->data[self::BASE_PRICE] = $price;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBasePrice()
    {
        return $this->data[self::BASE_PRICE];
    }

    /**
     * @param $cost
     * @return $this
     */
    public function setCost($cost)
    {
        $this->data[self::COST] = $cost;
        return $this;
    }

    /**
     * @param $handlingCost
     * @return $this
     */
    public function setHandlingCost($handlingCost)
    {
        $this->data[self::HANDLING_COST] = $handlingCost;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHandlingCost()
    {
        return $this->data[self::HANDLING_COST];
    }

    /**
     * @param string $company
     * @return $this
     */
    public function setCompany($company)
    {
        $this->data[self::COMPANY] = $company;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->data[self::COMPANY];
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->data[self::METHOD] = $method;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->data[self::METHOD];
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->data[self::TITLE] = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->data[self::TITLE];
    }

    /**
     * @param int $sort
     * @return $this
     */
    public function setSort($sort)
    {
        $this->data[self::SORT] = $sort;
        return $this;
    }

    /**
     * @return int
     */
    public function getSort()
    {
        return $this->data[self::SORT];
    }

    /**
     * @param array $productIds
     * @return $this
     */
    public function setProductIds(array $productIds)
    {
        $this->data[self::PRODUCT_IDS] = $productIds;
        return $this;
    }

    /**
     * @return array
     */
    public function getProductIds()
    {
        return $this->data[self::PRODUCT_IDS];
    }

    /**
     * @param array $skus
     * @return $this
     */
    public function setSkus(array $skus)
    {
        $this->data[self::SKUS] = $skus;
        return $this;
    }

    /**
     * @return array
     */
    public function getSkus()
    {
        return $this->data[self::SKUS];
    }
}
