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

    const MSG_INFO = 'info';
    const MSG_SUCCESS = 'success';
    const MSG_WARNING = 'warning';
    const MSG_ERROR = 'danger';

    /**
     * Data
     *
     * @var array
     * @access public
     */
    public $data = [];

    /**
     * @var array
     */
    public $messages = [];

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
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Common convention in listeners. Made a method for it
     *
     * @return array
     */
    public function getReturnData()
    {
        if (isset($this->data['return_data'])
            && is_array($this->data['return_data'])
        ) {
            return $this->data['return_data'];
        }

        return [];
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
    public function fromArray(array $data)
    {
        //ensuring that defaults are preserved
        foreach($data as $key => $value) {
            $this->data[$key] = $value;
        }
        return $this;
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
}
