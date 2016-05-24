<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContentSlot
 *
 * @ORM\Table(name="content_slot")
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\ContentSlotRepository")
 */
class ContentSlot
    implements CartEntityInterface
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
     * @var integer $old_id
     *
     * @ORM\Column(name="old_id", type="integer", nullable=true)
     */
    private $old_id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="text", nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="body_text", type="text", nullable=true)
     */
    private $body_text;

    /**
     * @var integer
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    private $sort_order;

    /**
     * @var string
     *
     * @ORM\Column(name="content_type", type="string", length=16, nullable=true)
     */
    private $content_type;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="text", nullable=true)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="embed_code", type="text", nullable=true)
     */
    private $embed_code;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="text", nullable=true)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="alt_text", type="string", length=255, nullable=true)
     */
    private $alt_text;

    /**
     * @var \MobileCart\CoreBundle\Entity\Content
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Content", inversedBy="slots")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     */
    private $parent;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    public function getObjectTypeName()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::CONTENT_SLOT;
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
        return $this->getBaseData();
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'old_id' => $this->getOldId(),
            'title' => $this->getTitle(),
            'body_text' => $this->getBodyText(),
            'sort_order' => $this->getSortOrder(),
            'content_type' => $this->getContentType(),
            'url' => $this->getUrl(),
            'embed_code' => $this->getEmbedCode(),
            'path' => $this->getPath(),
            'alt_text' => $this->getAltText(),
        ];
    }

    /**
     * @param int $oldId
     * @return Content
     */
    public function setOldId($oldId)
    {
        $this->old_id = $oldId;
        return $this;
    }

    /**
     * @return int
     */
    public function getOldId()
    {
        return $this->old_id;
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param $bodyText
     * @return $this
     */
    public function setBodyText($bodyText)
    {
        $this->body_text = $bodyText;
        return $this;
    }

    /**
     * @return string
     */
    public function getBodyText()
    {
        return $this->body_text;
    }

    /**
     * Set sort_order
     *
     * @param integer $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        $this->sort_order = $sortOrder;
        return $this;
    }

    /**
     * Get sort_order
     *
     * @return integer
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }

    /**
     * @param $contentType
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->content_type = $contentType;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->content_type;
    }

    /**
     * @param $embedCode
     * @return $this
     */
    public function setEmbedCode($embedCode)
    {
        $this->embed_code = $embedCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmbedCode()
    {
        return $this->embed_code;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return ContentSlot
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set altText
     *
     * @param string $altText
     * @return $this
     */
    public function setAltText($altText)
    {
        $this->alt_text = $altText;
        return $this;
    }

    /**
     * Get altText
     *
     * @return string 
     */
    public function getAltText()
    {
        return $this->alt_text;
    }

    /**
     * Set parent
     *
     * @param \MobileCart\CoreBundle\Entity\Content $parent
     * @return $this;
     */
    public function setParent(Content $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Get parent
     *
     * @return \MobileCart\CoreBundle\Entity\Content
     */
    public function getParent()
    {
        return $this->parent;
    }
}
