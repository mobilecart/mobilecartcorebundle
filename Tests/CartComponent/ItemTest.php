<?php

namespace MobileCart\CoreBundle\Tests\CartComponent;

use PHPUnit\Framework\TestCase;
use MobileCart\CoreBundle\CartComponent\Item;

class ItemTest extends TestCase
{
    public function testSetGetId()
    {
        $int = 23;
        $item = new Item();
        $item->setId($int);
        $this->assertEquals($int, $item->getId());
    }

    public function testSetGetProductId()
    {
        $int = 23;
        $item = new Item();
        $item->setProductId($int);
        $this->assertEquals($int, $item->getProductId());
    }

    public function testSetGetSku()
    {
        $str = 'asdf-1234';
        $item = new Item();
        $item->setSku($str);
        $this->assertEquals($str, $item->getSku());
    }

    public function testSetGetName()
    {
        $str = 'asdf-1234';
        $item = new Item();
        $item->setName($str);
        $this->assertEquals($str, $item->getName());
    }

    public function testSetGetSlug()
    {
        $str = 'asdf-1234';
        $item = new Item();
        $item->setSlug($str);
        $this->assertEquals($str, $item->getSlug());
    }

    public function testSetGetCurrency()
    {
        $str = 'EUR';
        $item = new Item();
        $item->setCurrency($str);
        $this->assertEquals($str, $item->getCurrency());
    }

    public function testSetGetPrice()
    {
        $str = '11.23';
        $item = new Item();
        $item->setPrice($str);
        $this->assertEquals($str, $item->getPrice());
    }

    public function testSetGetTax()
    {
        $str = '11.23';
        $item = new Item();
        $item->setTax($str);
        $this->assertEquals($str, $item->getTax());
    }

    public function testSetGetDiscount()
    {
        $str = '11.23';
        $item = new Item();
        $item->setDiscount($str);
        $this->assertEquals($str, $item->getDiscount());
    }

    public function testSetGetBaseCurrency()
    {
        $str = 'EUR';
        $item = new Item();
        $item->setBaseCurrency($str);
        $this->assertEquals($str, $item->getBaseCurrency());
    }

    public function testSetGetBasePrice()
    {
        $str = '11.23';
        $item = new Item();
        $item->setBasePrice($str);
        $this->assertEquals($str, $item->getBasePrice());
    }

    public function testSetGetBaseTax()
    {
        $str = '11.23';
        $item = new Item();
        $item->setBaseTax($str);
        $this->assertEquals($str, $item->getBaseTax());
    }

    public function testSetGetBaseDiscount()
    {
        $str = '11.23';
        $item = new Item();
        $item->setBaseDiscount($str);
        $this->assertEquals($str, $item->getBaseDiscount());
    }

    public function testSetGetQty()
    {
        $int = 23;
        $item = new Item();
        $item->setQty($int);
        $this->assertEquals($int, $item->getQty());
    }

    public function testSetGetMinQty()
    {
        $int = 23;
        $item = new Item();
        $item->setMinQty($int);
        $this->assertEquals($int, $item->getMinQty());
    }

    public function testSetGetAvailQty()
    {
        $int = 23;
        $item = new Item();
        $item->setAvailQty($int);
        $this->assertEquals($int, $item->getAvailQty());
    }

    public function testSetGetPromoQty()
    {
        $int = 23;
        $item = new Item();
        $item->setPromoQty($int);
        $this->assertEquals($int, $item->getPromoQty());
    }

    public function testSetGetOrigQty()
    {
        $int = 23;
        $item = new Item();
        $item->setOrigQty($int);
        $this->assertEquals($int, $item->getOrigQty());
    }

    public function testSetGetCustom()
    {
        $str = 'class of 2000';
        $item = new Item();
        $item->setCustom($str);
        $this->assertEquals($str, $item->getCustom());
    }

    public function testSetGetWeight()
    {
        $str = '11.23';
        $item = new Item();
        $item->setWeight($str);
        $this->assertEquals($str, $item->getWeight());
    }

    public function testSetGetWeightUnit()
    {
        $str = 'lbs';
        $item = new Item();
        $item->setWeightUnit($str);
        $this->assertEquals($str, $item->getWeightUnit());
    }

    public function testSetGetWidth()
    {
        $str = '11.23';
        $item = new Item();
        $item->setWidth($str);
        $this->assertEquals($str, $item->getWidth());
    }

    public function testSetGetHeight()
    {
        $str = '11.23';
        $item = new Item();
        $item->setHeight($str);
        $this->assertEquals($str, $item->getHeight());
    }

    public function testSetGetLength()
    {
        $str = '11.23';
        $item = new Item();
        $item->setLength($str);
        $this->assertEquals($str, $item->getLength());
    }

    public function testSetGetMeasureUnit()
    {
        $str = 'in';
        $item = new Item();
        $item->setMeasureUnit($str);
        $this->assertEquals($str, $item->getMeasureUnit());
    }

    public function testSetGetIsTaxableWhenTrue()
    {
        $bool = true;
        $item = new Item();
        $item->setIsTaxable($bool);
        $this->assertEquals($bool, $item->getIsTaxable());
    }

    public function testSetGetIsTaxableWhenFalse()
    {
        $bool = false;
        $item = new Item();
        $item->setIsTaxable($bool);
        $this->assertEquals($bool, $item->getIsTaxable());
    }

    public function testSetGetIsDiscountableWhenTrue()
    {
        $bool = true;
        $item = new Item();
        $item->setIsDiscountable($bool);
        $this->assertEquals($bool, $item->getIsDiscountable());
    }

    public function testSetGetIsDiscountableWhenFalse()
    {
        $bool = false;
        $item = new Item();
        $item->setIsDiscountable($bool);
        $this->assertEquals($bool, $item->getIsDiscountable());
    }

    public function testSetGetIsEnabledWhenTrue()
    {
        $bool = true;
        $item = new Item();
        $item->setIsEnabled($bool);
        $this->assertEquals($bool, $item->getIsEnabled());
    }

    public function testSetGetIsEnabledWhenFalse()
    {
        $bool = false;
        $item = new Item();
        $item->setIsEnabled($bool);
        $this->assertEquals($bool, $item->getIsEnabled());
    }

    public function testSetGetIsInStockWhenTrue()
    {
        $bool = true;
        $item = new Item();
        $item->setIsInStock($bool);
        $this->assertEquals($bool, $item->getIsInStock());
    }

    public function testSetGetIsInStockWhenFalse()
    {
        $bool = false;
        $item = new Item();
        $item->setIsInStock($bool);
        $this->assertEquals($bool, $item->getIsInStock());
    }

    public function testSetGetIsQtyManagedWhenTrue()
    {
        $bool = true;
        $item = new Item();
        $item->setIsQtyManaged($bool);
        $this->assertEquals($bool, $item->getIsQtyManaged());
    }

    public function testSetGetIsQtyManagedWhenFalse()
    {
        $bool = false;
        $item = new Item();
        $item->setIsQtyManaged($bool);
        $this->assertEquals($bool, $item->getIsQtyManaged());
    }

    public function testSetGetIsFlatShippingWhenTrue()
    {
        $bool = true;
        $item = new Item();
        $item->setIsFlatShipping($bool);
        $this->assertEquals($bool, $item->getIsFlatShipping());
    }

    public function testSetGetIsFlatShippingWhenFalse()
    {
        $bool = false;
        $item = new Item();
        $item->setIsFlatShipping($bool);
        $this->assertEquals($bool, $item->getIsFlatShipping());
    }

    public function testSetGetFlatShippingPrice()
    {
        $str = '11.23';
        $item = new Item();
        $item->setFlatShippingPrice($str);
        $this->assertEquals($str, $item->getFlatShippingPrice());
    }
}
