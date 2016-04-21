<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SearchTerm
 *
 * @ORM\Table(name="search_term")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\SearchTermRepository")
 */
class SearchTerm
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="raw_query", type="string", length=255)
     */
    private $raw_query;

    /**
     * @var string
     *
     * @ORM\Column(name="sanitized_query", type="string", length=255)
     */
    private $sanitized_query;

    /**
     * @var int
     *
     * @ORM\Column(name="usage_count", type="integer")
     */
    private $usage_count;

    /**
     * @var int
     *
     * @ORM\Column(name="result_count", type="integer")
     */
    private $result_count;


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
     * Set raw_query
     *
     * @param string $rawQuery
     * @return SearchTerm
     */
    public function setRawQuery($rawQuery)
    {
        $this->raw_query = $rawQuery;
        return $this;
    }

    /**
     * Get raw_query
     *
     * @return string 
     */
    public function getRawQuery()
    {
        return $this->raw_query;
    }

    /**
     * Set sanitized_query
     *
     * @param string $sanitizedQuery
     * @return SearchTerm
     */
    public function setSanitizedQuery($sanitizedQuery)
    {
        $this->sanitized_query = $sanitizedQuery;
        return $this;
    }

    /**
     * Get sanitized_query
     *
     * @return string 
     */
    public function getSanitizedQuery()
    {
        return $this->sanitized_query;
    }

    /**
     * Set usage_count
     *
     * @param integer $usageCount
     * @return SearchTerm
     */
    public function setUsageCount($usageCount)
    {
        $this->usage_count = $usageCount;
        return $this;
    }

    /**
     * Get usage_count
     *
     * @return integer 
     */
    public function getUsageCount()
    {
        return $this->usage_count;
    }

    /**
     * Set result_count
     *
     * @param integer $resultCount
     * @return SearchTerm
     */
    public function setResultCount($resultCount)
    {
        $this->result_count = $resultCount;
        return $this;
    }

    /**
     * Get result_count
     *
     * @return integer 
     */
    public function getResultCount()
    {
        return $this->result_count;
    }
}
