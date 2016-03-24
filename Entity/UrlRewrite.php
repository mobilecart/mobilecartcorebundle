<?php

namespace MobileCart\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UrlRewrite
 *
 * @ORM\Table(name="url_rewrite", indexes={@ORM\Index(name="request_uri_idx", columns={"request_uri"})})
 * @ORM\Entity(repositoryClass="MobileCart\CoreBundle\Entity\UrlRewriteRepository")
 */
class UrlRewrite
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
     * @var string
     *
     * @ORM\Column(name="object_type", type="string", length=32)
     */
    private $object_type;

    /**
     * @var string
     *
     * @ORM\Column(name="object_action", type="string", length=8, nullable=true)
     */
    private $object_action;

    /**
     * @var string
     *
     * @ORM\Column(name="request_uri", type="string", length=255)
     */
    private $request_uri;

    /**
     * @var string
     *
     * @ORM\Column(name="params_json", type="text", nullable=true)
     */
    private $params_json;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_redirect", type="boolean", nullable=true)
     */
    private $is_redirect;

    /**
     * @var string
     *
     * @ORM\Column(name="redirect_url", type="text", nullable=true)
     */
    private $redirect_url;

    public function getObjectTypeName()
    {
        return \MobileCart\CoreBundle\Constants\EntityConstants::URL_REWRITE;
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
            'object_type' => $this->getObjectType(),
            'object_action' => $this->getObjectAction(),
            'request_uri' => $this->getRequestUri(),
            'params_json' => $this->getParamsJson(),
            'is_redirect' => $this->getIsRedirect(),
            'redirect_url' => $this->getRedirectUrl(),
        ];
    }

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
