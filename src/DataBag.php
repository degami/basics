<?php
/**
 * Degami Basics
 * PHP Version 7.0
 *
 * @category CMS / Framework
 * @package  Degami\Basics
 * @author   Mirko De Grandis <degami@github.com>
 * @license  MIT https://opensource.org/licenses/mit-license.php
 * @link     https://github.com/degami/basics
 */
namespace Degami\Basics;

use \Iterator;
use \ArrayIterator;
use \ArrayAccess;
use \Countable;
use \Degami\Basics\Traits\ToolsTrait;

/**
 * A class to hold data
 *
 * @abstract
 */
abstract class DataBag extends DataElement implements Iterator, ArrayAccess, Countable
{
    use ToolsTrait;

    /**
     * Current position
     *
     * @var integer
     */
    protected int $databag_current_position = -1;

    /**
     * Prefix for numeric keys
     *
     * @var string
     */
    protected string $numeric_keys_prefix = '_value';

    /**
     * Class constructor
     *
     * @param mixed $data data to add
     * @param array $options construct options
     */
    public function __construct(mixed $data, ?array $options = [])
    {
        if ($options == null) {
            $options = [];
        }

        unset($options['dataelement_data']);
        unset($options['databag_current_position']);

        $this->setClassProperties($options);

        $this->databag_current_position = -1;
        $this->add($data);
    }

    /**
     * Adds data to the element
     *
     * @param  array $data data to add
     * @return DataBag
     */
    public function add(array $data) : self
    {
        $data = array_combine(array_map(function($k) {
            return (is_numeric($k) ? $this->numeric_keys_prefix : '') . $k;
        }, array_keys($data)), array_values($data));

        return parent::add($data);
    }

    /**
     * Delete data by key
     *
     * @param  string $key key of data to remove
     * @return DataBag
     */
    public function delete(string $key) : self
    {
        $this->offsetUnset($key);
        return $this;
    }

    /**
     * Check if data is contained
     *
     * @param  string $key key of data to check
     * @return boolean
     */
    public function contains($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Rewind pointer position
     */
    public function rewind() : void
    {
        $this->databag_current_position = 0;
    }

    /**
     * Get data keys
     *
     * @return array data keys
     */
    private function getKeys()
    {
        return array_keys($this->dataelement_data);
    }

    /**
     * Get data
     *
     * @return array data
     */
    public function getData() : array
    {
        return $this->dataelement_data;
    }

    /**
     * Get current element
     *
     * @return mixed current element
     */
    public function current() : mixed
    {
        $keys = $this->getKeys();
        if (!isset($keys[$this->databag_current_position])) {
            return false;
        }
        return $this->dataelement_data[ $keys[$this->databag_current_position] ];
    }

    /**
     * Get current position key
     *
     * @return string key
     */
    public function key() : string
    {
        $keys = $this->getKeys();
        return $keys[ $this->databag_current_position ];
    }

    /**
     * Increment current position
     */
    public function next() : void
    {
        ++$this->databag_current_position;
    }

    /**
     * Check if current position is valud
     *
     * @return boolean current position is valid
     */
    public function valid() : bool
    {
        $keys = $this->getKeys();
        if (!isset($keys[$this->databag_current_position])) {
            return false;
        }
        return isset($this->dataelement_data[ $keys[$this->databag_current_position] ]);
    }

    /**
     * __sleep magic method
     *
     * @return array
     */
    public function __sleep() : array
    {
        return ['data'];
    }

    /**
     * Set_state magic method
     *
     * @param $an_array
     *
     * @return DataBag
     */
    public static function __set_state($an_array) : DataBag
    {
        $obj = new static($an_array);
        return $obj;
    }

    /**
     * Gets data iterator
     *
     * @return ArrayIterator
     */
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this);
    }

    /**
     * Gets data keys
     *
     * @return array data keys
     */
    public function keys() : array
    {
        return $this->getKeys();
    }

    /**
     * Set data by key
     *
     * @param mixed $offset key
     * @param mixed  $value  data to set
     */
    public function offsetSet(mixed $offset, mixed $value) : void
    {
        $this->__set($offset, $value);
    }

    /**
     * Check if data exists bu key
     *
     * @param  string $offset key to check
     * @return boolean data exists
     */
    public function offsetExists(mixed $offset) : bool
    {
        return $this->__isset($offset);
    }

    /**
     * removes data by key
     *
     * @param string $offset key to delete
     */
    public function offsetUnset(mixed $offset) : void
    {
        $this->__unset($offset);
    }

    /**
     * Gets data by key
     *
     * @param  string $offset key to get
     * @return mixed|null
     */
    public function offsetGet(mixed $offset) : mixed
    {
        return $this->__get($offset);
    }

    /**
     * Gets data as array
     *
     * @return array
     */
    public function toArray() : array
    {
        $out = [];
        $this->checkDataArr();
        foreach ($this->dataelement_data as $key => $value) {
            $out[$key] = (is_object($value) && method_exists($value, 'toArray')) ?
                            $value->toArray() :
                            $value;
        }
        return $out;
    }

    /**
     * Gets an array with the selected keys
     *
     * @param  array $keys keys to get
     * @return array
     */
    public function only(array $keys) : array
    {
        $out = [];
        if (empty($keys)) {
            return $this->toArray();
        }
        foreach ($this->toArray() as $k => $v) {
            if (in_array($k, $keys)) {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    /**
     * Gets data size
     *
     * @return integer
     */
    public function count() : int
    {
        return count($this->dataelement_data);
    }

    /**
     * Check "data" property to be an array
     *
     * @return DataBag
     */
    protected function checkDataArr() : self
    {
        if (!is_array($this->dataelement_data)) {
            if (!empty($this->dataelement_data)) {
                $this->dataelement_data = [ '_value0' => $this->dataelement_data ];
            } else {
                $this->dataelement_data = [];
            }
        }
        return $this;
    }

    /**
     * Drops "data" contents
     *
     * @return DataBag
     */
    public function clear() : self
    {
        foreach ($this->getKeys() as $key) {
            $this->__unset($key);
        }
        return $this;
    }
}
