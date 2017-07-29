<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UrlRewrite
 *
 * @ORM\Table(name="url_rewrite", indexes={@ORM\Index(name="request_uri_idx", columns={"request_uri"})})
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Repository\UrlRewriteRepository")
 */
class UrlRewrite
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
     * @var string
     *
     * @ORM\Column(name="object_type", type="string", length=32)
     */
    protected $object_type;

    /**
     * @var string
     *
     * @ORM\Column(name="object_action", type="string", length=8, nullable=true)
     */
    protected $object_action;

    /**
     * @var string
     *
     * @ORM\Column(name="request_uri", type="string", length=255)
     */
    protected $request_uri;

    /**
     * @var string
     *
     * @ORM\Column(name="params_json", type="text", nullable=true)
     */
    protected $params_json;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_redirect", type="boolean", nullable=true)
     */
    protected $is_redirect;

    /**
     * @var string
     *
     * @ORM\Column(name="redirect_url", type="text", nullable=true)
     */
    protected $redirect_url;

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
        return \MobileCart\CoreBundle\Constants\EntityConstants::URL_REWRITE;
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'id' => $this->getId(),
            'object_type' => $this->getObjectType(),
            'object_action' => $this->getObjectAction(),
            'request_uri' => $this->getRequestUri(),
            'params_json' => $this->getParamsJson(),
            'is_redirect' => $this->getIsRedirect(),
            'redirect_url' => $this->getRedirectUrl(),
        ];
    }

    /**
     * Set object_type
     *
     * @param string $objectType
     * @return $this
     */
    public function setObjectType($objectType)
    {
        $this->object_type = $objectType;

        return $this;
    }

    /**
     * Get object_type
     *
     * @return string 
     */
    public function getObjectType()
    {
        return $this->object_type;
    }

    /**
     * Set object_action
     *
     * @param string $objectAction
     * @return $this
     */
    public function setObjectAction($objectAction)
    {
        $this->object_action = $objectAction;

        return $this;
    }

    /**
     * Get object_action
     *
     * @return string 
     */
    public function getObjectAction()
    {
        return $this->object_action;
    }

    /**
     * Set request_uri
     *
     * @param string $requestUri
     * @return $this
     */
    public function setRequestUri($requestUri)
    {
        $this->request_uri = $requestUri;
        return $this;
    }

    /**
     * Get request_uri
     *
     * @return string 
     */
    public function getRequestUri()
    {
        return $this->request_uri;
    }

    /**
     * Set params_json
     *
     * @param string $paramsJson
     * @return $this
     */
    public function setParamsJson($paramsJson)
    {
        $this->params_json = $paramsJson;
        return $this;
    }

    /**
     * Get params_json
     *
     * @return string 
     */
    public function getParamsJson()
    {
        return $this->params_json;
    }

    /**
     * Set is_redirect
     *
     * @param boolean $isRedirect
     * @return UrlRewrite
     */
    public function setIsRedirect($isRedirect)
    {
        $this->is_redirect = $isRedirect;
        return $this;
    }

    /**
     * Get is_redirect
     *
     * @return boolean 
     */
    public function getIsRedirect()
    {
        return $this->is_redirect;
    }

    /**
     * Set redirect_url
     *
     * @param string $redirectUrl
     * @return UrlRewrite
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->redirect_url = $redirectUrl;
        return $this;
    }

    /**
     * Get redirect_url
     *
     * @return string 
     */
    public function getRedirectUrl()
    {
        return $this->redirect_url;
    }
}
