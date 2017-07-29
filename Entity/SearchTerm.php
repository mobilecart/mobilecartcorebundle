<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use MobileCart\CoreBundle\Entity\CartEntityInterface;

/**
 * SearchTerm
 *
 * @ORM\Table(name="search_term")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\SearchTermRepository")
 */
class SearchTerm
    extends AbstractCartEntity
    implements CartEntityInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="term", type="string", length=255)
     */
    protected $term;

    /**
     * @var int
     *
     * @ORM\Column(name="usage_count", type="integer")
     */
    protected $usage_count;

    /**
     * @var int
     *
     * @ORM\Column(name="result_count", type="integer")
     */
    protected $result_count;

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::SEARCH_TERM;
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'term' => $this->getTerm(),
            'usage_count' => $this->getUsageCount(),
            'result_count' => $this->getResultCount(),
        ];
    }

    /**
     * Set term
     *
     * @param string $rawQuery
     * @return SearchTerm
     */
    public function setTerm($rawQuery)
    {
        $this->term = $rawQuery;
        return $this;
    }

    /**
     * Get term
     *
     * @return string 
     */
    public function getTerm()
    {
        return $this->term;
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
