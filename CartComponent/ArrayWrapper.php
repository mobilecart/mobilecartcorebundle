<?php

namespace MobileCart\CoreBundle\CartComponent;

class ArrayWrapper
    implements \ArrayAccess, \Serializable, \IteratorAggregate, \JsonSerializable
{
    /**
     * Data
     *
     * @var array
     * @access protected
     */
    protected $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @return array|mixed
     */
    public function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this);
    }

    /**
     * Implementation of ArrayAccess::offsetSet()
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php
     * @param string $offset
     * @param mixed $value
     * @return $this
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
        return $this;
    }

    /**
     * Implementation of ArrayAccess::offsetExists()
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    /**
     * Implementation of ArrayAccess::offsetUnset()
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php
     * @param string $offset
     * @return $this
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
        return $this;
    }

    /**
     * Implementation of ArrayAccess::offsetGet()
     *
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->data[$offset])
            ? $this->data[$offset]
            : null;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize($this->data);
    }

    /**
     * @param $data
     * @return $this
     */
    public function unserialize($data)
    {
        $this->data = unserialize($data);
        return $this;
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
     * @throws \Exception
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

                $key .= 's'; //plural naming convention for arrays

                // example:
                //  $this->addItem($item)
                //   looks like
                //  $this->data['items'][] = $item

                if (!isset($this->data[$key]) || !is_array($this->data[$key])) {
                    $this->data[$key] = [];
                }
                $this->data[$key][] = $value;
                return $this;
                break;
            case 'uns': // unsetSomething
                $key = $this->camelToSnake(str_replace('unset', '', $method));
                $key .= 's'; // plural naming convention
                if (isset($this->data[$key]) && isset($this->data[$key][$value])) {
                    unset($this->data[$key][$value]);
                }
                return $this;
                break;
            case 'has':
                return isset($this->data[$key]) && count($this->data[$key]) > 0;
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
        return array();
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
     * @param $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return isset($this->data[$key])
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
     * @return array
     */
    public function toArray()
    {
        // return iterator_to_array($this, true);
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
        //return json_encode($this->toArray());
        return json_encode($this->jsonSerialize());
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
