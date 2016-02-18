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

class StockService
{
    /**
     * @var
     */
    protected $entityService;

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
     * @param $cart
     * @return $this
     */
    public function submitCart($cart)
    {
        if (!$cart->getItems()) {
            return $this;
        }


    }

    /**
     * @param $productId
     * @param $qty
     * @return bool
     */
    public function canReduceStock($productId, $qty)
    {
        $isOk = false;

        return $isOk;
    }


    public function reduceStock($productId, $qty)
    {

        // throw new InsufficientStockException();

        // todo : observe event here

        $sql = "update product set qty = qty - {$qty} where id = ?";
    }
}
