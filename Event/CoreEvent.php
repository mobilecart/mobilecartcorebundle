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

    static $sections = [
        self::SECTION_FRONTEND,
        self::SECTION_BACKEND,
        self::SECTION_API, // todo: get rid of this and use $is_api
    ];

    const MSG_INFO = 'info';
    const MSG_SUCCESS = 'success';
    const MSG_WARNING = 'warning';
    const MSG_ERROR = 'danger';

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
     * @var mixed
     */
    protected $request;

    /**
     * @var \Symfony\Component\HttpFoundation\Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var string
     */
    protected $section = '';

    /**
     * @var bool
     */
    protected $is_mass_update = false;

    /**
     * @var bool
     */
    protected $is_api = false;

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
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return isset($this->data[$key])
            ? $this->data[$key]
            : null;
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
     * @return array
     */
    public function getReturnData()
    {
        return $this->return_data;
    }

    /**
     * @param $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
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
     * @param $section
     * @throws \Exception
     */
    public function setSection($section)
    {
        if (!in_array($section, self::$sections)) {
            throw new \Exception("Invalid Section");
        }

        $this->section = $section;
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
}
