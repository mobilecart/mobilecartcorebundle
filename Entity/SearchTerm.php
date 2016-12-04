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
    implements CartEntityInterface
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
     * @ORM\Column(name="term", type="string", length=255)
     */
    private $term;

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

    public function getObjectTypeKey()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::SEARCH_TERM;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $vars = get_object_vars($this);
        if (array_key_exists($key, $vars)) {
            $this->$key = $value;
        }

        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    public function fromArray($data)
    {
        if (!$data) {
            return $this;
        }

        foreach($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Lazy-loading getter
     *  ideal for usage in the View layer
     *
     * @param $key
     * @return mixed|null
     */
    public function get($key)
    {
        if (isset($this->$key)) {
            return $this->$key;
        }

        $data = $this->getBaseData();
        if (isset($data[$key])) {
            return $data[$key];
        }

        $data = $this->getData();
        if (isset($data[$key])) {

            if (is_array($data[$key])) {
                return implode(',', $data[$key]);
            }

            return $data[$key];
        }

        return '';
    }

    /**
     * Getter , after fully loading
     *  use only if necessary, and avoid calling multiple times
     *
     * @param string $key
     * @return array|null
     */
    public function getData($key = '')
    {
        $data = $this->getBaseData();

        if (strlen($key) > 0) {

            return isset($data[$key])
                ? $data[$key]
                : null;
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getLuceneVarValuesData()
    {
        // Note:
        // be careful with adding foreign relationships here
        // since it will add 1 query every time an item is loaded

        return $this->getBaseData();
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
