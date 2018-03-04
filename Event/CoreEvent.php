<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CoreEvent
 * @package MobileCart\CoreBundle\Event
 *
 * This class is similar to ArrayWrapper
 *  It uses magic setters/getters
 *
 */
class CoreEvent extends Event
{
    const SECTION_BACKEND = 'backend';
    const SECTION_FRONTEND = 'frontend';
    const SECTION_API = 'api';

    // todo : remove SECTION_API

    static $sections = [
        self::SECTION_FRONTEND,
        self::SECTION_BACKEND,
        self::SECTION_API
    ];

    const MSG_INFO = 'info';
    const MSG_SUCCESS = 'success';
    const MSG_WARNING = 'warning';
    const MSG_ERROR = 'error';

    const SUCCESS = 'success';
    const MESSAGES = 'messages';
    const CART = 'cart';

    const HTML = 'html';
    const JSON = 'application/json';
    const XML = 'application/xml';

    /**
     * Data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Return Data
     *
     * @var array
     */
    protected $return_data = [];

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $api_request = [];

    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response;

    /**
     * @var int
     */
    protected $response_code = 200;

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var string
     */
    protected $object_type;

    /**
     * @var string
     */
    protected $section = '';

    /**
     * @var string
     */
    protected $form_action = '';

    /**
     * @var string
     */
    protected $form_method = '';

    /**
     * @var array
     */
    protected $form_data = [];

    /**
     * @var bool
     */
    protected $is_form_submitted = false;

    /**
     * @var \Symfony\Component\Form\FormInterface
     */
    protected $form;

    /**
     * @var bool
     */
    protected $is_mass_update = false;

    /**
     * @var bool
     */
    protected $is_api = false;

    /**
     * @var \MobileCart\CoreBundle\Entity\CartEntityInterface
     */
    protected $entity;

    /**
     * @var bool
     */
    protected $success = false;

    /**
     * @var int
     */
    protected $count_success = 0;

    /**
     * @var int
     */
    protected $count_error = 0;

    /**
     * @var int
     */
    protected $count_warning = 0;

    /**
     * @var \Symfony\Component\Security\Core\User\UserInterface
     */
    protected $user;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * @param $key
     * @return string
     */
    public function camelToSnake($key)
    {
        return strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $key));
    }

    /**
     * Magic method . Catch all calls
     *
     * @param string $method
     * @param array $args
     * @return $this|mixed
     */
    public function __call($method, $args)
    {
        $key = $this->camelToSnake(substr($method, 3));
        $value = isset($args[0]) ? $args[0] : null;
        switch(substr($method, 0, 3)) {
            case 'set':
                return $this->set($key, $value);
                break;
            case 'get':
                return $this->get($key);
                break;
            case 'add':

                if (substr($key, -1) != 's') {
                    $key .= 's'; //plural naming convention for arrays
                }

                // eg
                // this->addItem($item)
                // this->data[items][] = $item

                if (!isset($this->data[$key])
                    || !is_array($this->data[$key])) {

                    $this->data[$key] = [];
                }

                $this->data[$key][] = $value;

                return $this;
                break;
            default:
                //no-op
                break;
        }

        // try to catch Twig calls
        if (array_key_exists($method, $this->data)) {
            return $this->data[$method];
        }

        return ''; //key wasn't found
    }

    /**
     * @return $this
     */
    public function reset()
    {
        return $this->fromArray($this->getDefaults());
    }

    /**
     * @return array
     */
    protected function getDefaults()
    {
        return [];
    }

    /**
     * @param $key
     * @return null
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * @return $this
     */
    public function flashMessages()
    {
        if ($this->getMessages()
            && $this->getRequest()
            && $this->getRequest()->getSession()
        ) {
            foreach($this->getMessages() as $code => $messages) {
                if (!$messages) {
                    continue;
                }
                foreach($messages as $message) {
                    $this->getRequest()->getSession()->getFlashBag()->add($code, $message);
                }
            }
        }

        return $this;
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->data)
            ? $this->data[$key]
            : $default;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @param mixed $param1
     * @param mixed $param2
     * @return $this
     */
    public function setData($param1, $param2 = null)
    {
        if (is_array($param1)) {
            $this->data = $param1;
        } elseif (is_scalar($param1)) {
            $this->data[$param1] = $param2;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function addData(array $data)
    {
        return $this->fromArray($data);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function fromArray(array $data)
    {
        //ensuring that defaults are preserved
        foreach($data as $key => $value) {
            $this->data[$key] = $value;
        }
        return $this;
    }

    /**
     * @param mixed $param1
     * @param mixed $param2
     * @return $this
     */
    public function setReturnData($param1, $param2 = null)
    {
        if (is_array($param1)) {
            $this->return_data = $param1;
        } elseif (is_scalar($param1)) {
            $this->return_data[$param1] = $param2;
        }

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function addReturnData(array $data)
    {
        if (!$data) {
            return $this;
        }

        foreach($data as $key => $value) {
            $this->return_data[$key] = $value;
        }

        return $this;
    }

    /**
     * @param string $key
     * @param null $default
     * @return array|null
     */
    public function getReturnData($key = '', $default = null)
    {
        if (strlen($key)) {

            return isset($this->return_data[$key])
                ? $this->return_data[$key]
                : $default;
        }

        return $this->return_data;
    }

    /**
     * @param bool $isSuccess
     * @param bool $updateReturnData
     * @return $this
     */
    public function setSuccess($isSuccess = true, $updateReturnData = true)
    {
        $this->success = (bool) $isSuccess;
        if ($updateReturnData) {
            $this->setReturnData(self::SUCCESS, $this->success);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function getSuccess()
    {
        return (bool) $this->success;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return $this
     */
    public function setRequest(\Symfony\Component\HttpFoundation\Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return array|string
     */
    public function getRequestAccept()
    {
        if ($this->getRequest()) {
            return $this->getRequest()->headers->get('Accept');
        }
        return '';
    }

    /**
     * @return array|string
     */
    public function getContentType()
    {
        if ($this->getRequest()) {
            return $this->getRequest()->headers->get('Content-Type');
        }
        return '';
    }

    /**
     * @return bool
     */
    public function isJsonResponse()
    {
        return is_int(strpos($this->getRequestAccept(), self::JSON))
                || $this->getContentType() == self::JSON;
    }

    /**
     * @param array $invalid
     * @return JsonResponse
     */
    public function getInvalidFormJsonResponse(array $invalid = [])
    {
        foreach($this->getForm()->all() as $childKey => $child) {
            $errors = $child->getErrors();
            if ($errors->count()) {
                $invalid[$childKey] = [];
                foreach($errors as $error) {
                    $invalid[$childKey][] = $error->getMessage();
                }
            }
        }

        return new JsonResponse([
            'success' => false,
            'invalid' => $invalid,
            'messages' => $this->getMessages(),
        ]);
    }

    /**
     * @param array $apiRequest
     * @return $this
     */
    public function setApiRequest(array $apiRequest)
    {
        $this->api_request = $apiRequest;
        return $this;
    }

    /**
     * @return array
     */
    public function getApiRequest()
    {
        return $this->api_request;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return $this
     */
    public function setResponse(\Symfony\Component\HttpFoundation\Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param $code
     * @return $this
     */
    public function setResponseCode($code)
    {
        $this->response_code = (int) $code;
        return $this;
    }

    /**
     * @return int
     */
    public function getResponseCode()
    {
        return (int) $this->response_code;
    }

    /**
     * @param array $messages
     * @return $this
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param $message
     * @return $this
     */
    public function addInfoMessage($message)
    {
        if (!isset($this->messages[self::MSG_INFO])
            ||!is_array($this->messages[self::MSG_INFO])) {

            $this->messages[self::MSG_INFO] = [];
        }
        $this->messages[self::MSG_INFO][] = $message;
        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    public function addSuccessMessage($message)
    {
        if (!isset($this->messages[self::MSG_SUCCESS])
            || !is_array($this->messages[self::MSG_SUCCESS])) {

            $this->messages[self::MSG_SUCCESS] = [];
        }
        $this->messages[self::MSG_SUCCESS][] = $message;
        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    public function addWarningMessage($message)
    {
        if (!isset($this->messages[self::MSG_WARNING])
            || !is_array($this->messages[self::MSG_WARNING])) {

            $this->messages[self::MSG_WARNING] = [];
        }
        $this->messages[self::MSG_WARNING][] = $message;
        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    public function addErrorMessage($message)
    {
        if (!isset($this->messages[self::MSG_ERROR])
            || !is_array($this->messages[self::MSG_ERROR])) {

            $this->messages[self::MSG_ERROR] = [];
        }
        $this->messages[self::MSG_ERROR][] = $message;
        return $this;
    }

    /**
     * @param $count
     * @return $this
     */
    public function setCountSuccess($count)
    {
        $this->count_success = $count;
        return $this;
    }

    /**
     * @param int $count
     * @return $this
     */
    public function addCountSuccess($count = 1)
    {
        $this->count_success += $count;
        return $this;
    }

    /**
     * @return int
     */
    public function getCountSuccess()
    {
        return $this->count_success;
    }

    /**
     * @param $count
     * @return $this
     */
    public function setCountError($count)
    {
        $this->count_error = $count;
        return $this;
    }

    /**
     * @param int $count
     * @return $this
     */
    public function addCountError($count = 1)
    {
        $this->count_error += $count;
        return $this;
    }

    /**
     * @return int
     */
    public function getCountError()
    {
        return $this->count_error;
    }

    /**
     * @param $count
     * @return $this
     */
    public function setCountWarning($count)
    {
        $this->count_warning = $count;
        return $this;
    }

    /**
     * @param int $count
     * @return $this
     */
    public function addCountWarning($count = 1)
    {
        $this->count_warning += $count;
        return $this;
    }

    /**
     * @return int
     */
    public function getCountWarning()
    {
        return $this->count_warning;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * @param $json
     * @return $this
     */
    public function fromJson($json)
    {
        return $this->fromArray((array) json_decode($json));
    }

    /**
     * @param $objectType
     * @return $this
     */
    public function setObjectType($objectType)
    {
        $this->object_type = $objectType;
        return $this;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->object_type;
    }

    /**
     * @param \MobileCart\CoreBundle\Entity\CartEntityInterface $entity
     * @return $this
     */
    public function setEntity(\MobileCart\CoreBundle\Entity\CartEntityInterface $entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @return \MobileCart\CoreBundle\Entity\CartEntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setFormMethod($method)
    {
        $this->form_method = $method;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormMethod()
    {
        return $this->form_method;
    }

    /**
     * @param $action
     * @return $this
     */
    public function setFormAction($action)
    {
        $this->form_action = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormAction()
    {
        return $this->form_action;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setFormData(array $data)
    {
        $this->form_data = $data;
        return $this;
    }

    /**
     * @return array|string
     */
    public function getFormData($key = '', $default = '')
    {
        if ($key) {
            return isset($this->form_data[$key])
                ? $this->form_data[$key]
                : $default;
        }

        return $this->form_data;
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     * @return $this
     */
    public function setForm(\Symfony\Component\Form\FormInterface $form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Submit Form and set the submitted Form Data
     *
     * @param array $formData
     * @return $this
     * @throws \Exception
     */
    public function submitForm(array $formData = [])
    {
        if (!$this->getForm()) {
            throw new \Exception("Form not set");
        }

        if (!$this->getRequest()) {
            throw new \Exception("Request not set");
        }

        switch($this->getContentType()) {
            case self::JSON:
                if ($formData) {
                    $this->setFormData($formData);
                } elseif (!$this->getFormData()) {
                    $formData = @ (array) json_decode($this->getRequest()->getContent());
                    if ($formData) {
                        foreach($formData as $key => $value) {
                            if ($value instanceof \stdClass) {
                                $formData[$key] = (array) $value;
                            }
                        }
                    }
                    $this->setFormData($formData);
                }
                break;
            default:
                if ($formData) {
                    $this->setFormData($formData);
                } elseif (!$this->getFormData()) {
                    $this->setFormData($this->getRequest()->request->get($this->getForm()->getName()));
                }
                break;
        }

        $this->getForm()->submit($this->getFormData());

        return $this;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isFormSubmitted()
    {
        if (!$this->getForm()) {
            throw new \Exception("Form not set");
        }

        return $this->getForm()->isSubmitted();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function isFormValid()
    {
        if (!$this->isFormSubmitted()) {
            $this->submitForm();
        }

        return $this->getForm()->isValid();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getFormInvalid()
    {
        if (!$this->getForm()) {
            throw new \Exception("Form not set");
        }

        if (!$this->getForm()->isSubmitted()) {
            throw new \Exception("Form not submitted");
        }

        $invalid = [];
        foreach($this->getForm()->all() as $childKey => $child) {
            $errors = $child->getErrors();
            if ($errors->count()) {
                if (!isset($invalid[$childKey])) {
                    $invalid[$childKey] = [];
                }
                foreach($errors as $error) {
                    $invalid[$childKey][] = $error->getMessage();
                }
            }
        }

        return $invalid;
    }

    /**
     * @param $section
     * @return $this
     * @throws \Exception
     */
    public function setSection($section)
    {
        if (!in_array($section, self::$sections)) {
            throw new \Exception("Invalid Section");
        }

        $this->section = $section;
        return $this;
    }

    /**
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param $yesNo
     * @return $this
     */
    public function setIsMassUpdate($yesNo)
    {
        $this->is_mass_update = $yesNo;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsMassUpdate()
    {
        return $this->is_mass_update;
    }

    /**
     * @param $isApi
     * @return $this
     */
    public function setIsApi($isApi)
    {
        $this->is_api = $isApi;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsApi()
    {
        return $this->is_api;
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface|null $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
