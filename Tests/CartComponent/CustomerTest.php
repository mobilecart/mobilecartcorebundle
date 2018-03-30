<?php

namespace MobileCart\CoreBundle\Tests\CartComponent;

use PHPUnit\Framework\TestCase;
use MobileCart\CoreBundle\CartComponent\Customer;

class CustomerTest extends TestCase
{
    public function testSetGetId()
    {
        $int = 23;
        $customer = new Customer();
        $customer->setId($int);
        $this->assertEquals($int, $customer->getId());
    }

    public function testSetGetEmail()
    {
        $str = 'test@fake.com';
        $customer = new Customer();
        $customer->setEmail($str);
        $this->assertEquals($str, $customer->getEmail());
    }

    public function testSetGetBillingFirstname()
    {
        $str = 'Albert';
        $customer = new Customer();
        $customer->setBillingFirstname($str);
        $this->assertEquals($str, $customer->getBillingFirstname());
    }

    public function testSetGetBillingLastname()
    {
        $str = 'Einstein';
        $customer = new Customer();
        $customer->setBillingLastname($str);
        $this->assertEquals($str, $customer->getBillingLastname());
    }

    public function testSetGetBillingCompany()
    {
        $str = 'ABC 123';
        $customer = new Customer();
        $customer->setBillingCompany($str);
        $this->assertEquals($str, $customer->getBillingCompany());
    }

    public function testSetGetBillingPhone()
    {
        $str = '333-222-4444';
        $customer = new Customer();
        $customer->setBillingPhone($str);
        $this->assertEquals($str, $customer->getBillingPhone());
    }

    public function testSetGetBillingStreet()
    {
        $str = '123 Test St';
        $customer = new Customer();
        $customer->setBillingStreet($str);
        $this->assertEquals($str, $customer->getBillingStreet());
    }

    public function testSetGetBillingStreet2()
    {
        $str = 'APT # 45';
        $customer = new Customer();
        $customer->setBillingStreet2($str);
        $this->assertEquals($str, $customer->getBillingStreet2());
    }

    public function testSetGetBillingCity()
    {
        $str = 'Duluth';
        $customer = new Customer();
        $customer->setBillingCity($str);
        $this->assertEquals($str, $customer->getBillingCity());
    }

    public function testSetGetBillingRegion()
    {
        $str = 'MN';
        $customer = new Customer();
        $customer->setBillingRegion($str);
        $this->assertEquals($str, $customer->getBillingRegion());
    }

    public function testSetGetBillingPostcode()
    {
        $str = '55806';
        $customer = new Customer();
        $customer->setBillingPostcode($str);
        $this->assertEquals($str, $customer->getBillingPostcode());
    }

    public function testSetGetBillingCountryId()
    {
        $str = 'US';
        $customer = new Customer();
        $customer->setBillingCountryId($str);
        $this->assertEquals($str, $customer->getBillingCountryId());
    }

    public function testSetGetIsShippingSameWhenTrue()
    {
        $bool = true;
        $item = new Customer();
        $item->setIsShippingSame($bool);
        $this->assertEquals($bool, $item->getIsShippingSame());
    }

    public function testSetGetIsShippingSameWhenFalse()
    {
        $bool = false;
        $item = new Customer();
        $item->setIsShippingSame($bool);
        $this->assertEquals($bool, $item->getIsShippingSame());
    }

    public function testSetGetShippingFirstname()
    {
        $str = 'Albert';
        $customer = new Customer();
        $customer->setShippingFirstname($str);
        $this->assertEquals($str, $customer->getShippingFirstname());
    }

    public function testSetGetShippingLastname()
    {
        $str = 'Einstein';
        $customer = new Customer();
        $customer->setShippingLastname($str);
        $this->assertEquals($str, $customer->getShippingLastname());
    }

    public function testSetGetShippingCompany()
    {
        $str = 'ABC 123';
        $customer = new Customer();
        $customer->setShippingCompany($str);
        $this->assertEquals($str, $customer->getShippingCompany());
    }

    public function testSetGetShippingPhone()
    {
        $str = '333-222-4444';
        $customer = new Customer();
        $customer->setShippingPhone($str);
        $this->assertEquals($str, $customer->getShippingPhone());
    }

    public function testSetGetShippingStreet()
    {
        $str = '123 Test St';
        $customer = new Customer();
        $customer->setShippingStreet($str);
        $this->assertEquals($str, $customer->getShippingStreet());
    }

    public function testSetGetShippingStreet2()
    {
        $str = 'APT # 45';
        $customer = new Customer();
        $customer->setShippingStreet2($str);
        $this->assertEquals($str, $customer->getShippingStreet2());
    }

    public function testSetGetShippingCity()
    {
        $str = 'Duluth';
        $customer = new Customer();
        $customer->setShippingCity($str);
        $this->assertEquals($str, $customer->getShippingCity());
    }

    public function testSetGetShippingRegion()
    {
        $str = 'MN';
        $customer = new Customer();
        $customer->setShippingRegion($str);
        $this->assertEquals($str, $customer->getShippingRegion());
    }

    public function testSetGetShippingPostcode()
    {
        $str = '55806';
        $customer = new Customer();
        $customer->setShippingPostcode($str);
        $this->assertEquals($str, $customer->getShippingPostcode());
    }

    public function testSetGetShippingCountryId()
    {
        $str = 'US';
        $customer = new Customer();
        $customer->setShippingCountryId($str);
        $this->assertEquals($str, $customer->getShippingCountryId());
    }
}
