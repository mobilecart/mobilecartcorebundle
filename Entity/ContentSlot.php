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
    extends AbstractCartEntity
    implements CartEntityInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer $old_id
     *
     * @ORM\Column(name="old_id", type="integer", nullable=true)
     */
    protected $old_id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="text", nullable=true)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(name="body_text", type="text", nullable=true)
     */
    protected $body_text;

    /**
     * @var integer
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    protected $sort_order;

    /**
     * @var string
     *
     * @ORM\Column(name="content_type", type="string", length=16, nullable=true)
     */
    protected $content_type;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="text", nullable=true)
     */
    protected $url;

    /**
     * @var string
     *
     * @ORM\Column(name="embed_code", type="text", nullable=true)
     */
    protected $embed_code;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="text", nullable=true)
     */
    protected $path;

    /**
     * @var string
     *
     * @ORM\Column(name="alt_text", type="string", length=255, nullable=true)
     */
    protected $alt_text;

    /**
     * @var \MobileCart\CoreBundle\Entity\Content
     *
     * @ORM\ManyToOne(targetEntity="MobileCart\CoreBundle\Entity\Content", inversedBy="slots")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * })
     */
    protected $parent;

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
        return \MobileCart\CoreBundle\Constants\EntityConstants::CONTENT_SLOT;
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
     * @return ContentSlot
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
