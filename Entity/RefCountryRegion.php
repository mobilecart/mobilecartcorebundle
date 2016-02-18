<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RefCountryRegion
 *
 * @ORM\Table(name="ref_country_region")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Entity\RefCountryRegionRepository")
 */
class RefCountryRegion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="country_code", type="string", length=2)
     */
    private $country_code;

    /**
     * @var string
     *
     * @ORM\Column(name="region_code", type="string", length=4)
     */
    private $region_code;

    /**
     * @var string
     *
     * @ORM\Column(name="region_name", type="string", length=255)
     */
    private $region_name;

    /**
     * @var string
     *
     * @ORM\Column(name="region_type", type="string", length=16)
     */
    private $region_type;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set country_code
     *
     * @param string $country_code
     * @return RefCountryRegion
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;

        return $this;
    }

    /**
     * Get country_code
     *
     * @return string 
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * Set region_code
     *
     * @param string $region_code
     * @return RefCountryRegion
     */
    public function setRegionCode($region_code)
    {
        $this->region_code = $region_code;

        return $this;
    }

    /**
     * Get region_code
     *
     * @return string 
     */
    public function getRegionCode()
    {
        return $this->region_code;
    }

    /**
     * Set region_name
     *
     * @param string $region_name
     * @return RefCountryRegion
     */
    public function setRegionName($region_name)
    {
        $this->region_name = $region_name;

        return $this;
    }

    /**
     * Get region_name
     *
     * @return string 
     */
    public function getRegionName()
    {
        return $this->region_name;
    }

    /**
     * Set region_type
     *
     * @param string $region_type
     * @return RefCountryRegion
     */
    public function setRegionType($region_type)
    {
        $this->region_type = $region_type;

        return $this;
    }

    /**
     * Get region_type
     *
     * @return string 
     */
    public function getRegionType()
    {
        return $this->region_type;
    }
}
