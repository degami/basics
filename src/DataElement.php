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

use Degami\Basics\Exceptions\BasicException;
use \Degami\Basics\Traits\ToolsTrait;

/**
 * A class to hold data
 *
 * @abstract
 */
abstract class DataElement
{
    use ToolsTrait;

    protected array $dataelement_data = [];
    
    /**
     * __get
     *
     * @param  mixed $key
     * @return mixed
     */
    public function __get(string $key) : mixed
    {
        return $this->dataelement_data[$key] ?? null;
    }
    
    /**
     * __set
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return self
     */
    public function __set(string $key, mixed $value) : void
    {
        if (property_exists(get_class($this), $key)) {
            throw new BasicException('Cannot define "'.$key.'" property');
        }

        $this->dataelement_data[$key] = $value;
    }
    
    /**
     * __isset
     *
     * @param  mixed $name
     * @return bool
     */
    public function __isset(string $name) : bool
    {
        return isset($this->dataelement_data[$name]);
    }

    
    /**
     * __unset
     *
     * @param  mixed $name
     * @return void
     */
    public function __unset(string $name) : void
    {
        unset($this->dataelement_data[$name]);
    }

    /**
     * Set/Get attribute wrapper
     *
     * @param  string $method
     * @param  array  $args
     * @return mixed
     * @throws BasicException
     */    
    public function __call(string $method, array $args) : mixed
    {
        switch (strtolower(substr($method, 0, 3))) {
            case 'get':
                $name = $this->PascalCaseToSnakeCase(trim(substr($method, 3)));
                return $this->{$name} ?? null;
                // no break
            case 'set':
                $name = $this->PascalCaseToSnakeCase(trim(substr($method, 3)));
                $this->{$name} = reset($args);
                return $this;
                // no break
            case 'has':
                $name = $this->PascalCaseToSnakeCase(trim(substr($method, 3)));
                if (array_key_exists($name, $this->dataelement_data)) {
                    return true;
                }
                return false;
        }
        throw new BasicException("Invalid method ".get_class($this)."::".$method."(".print_r($args, 1).")");
    }

    /**
     * gets data array
     *
     * @return array
     */
    public function getData() : array
    {
        return $this->dataelement_data;
    }

    /**
     * sets data array
     *
     * @param array $dataelement_data
     * @return DataElement
     */
    public function setData(array $dataelement_data) : self
    {
        $this->dataelement_data = $dataelement_data;

        return $this;
    }

    /**
     * Adds data to the element
     *
     * @param  array $data data to add
     * @return DataElement
     */
    public function add(array $data) : self
    {
        foreach ((array) $data as $k => $v) {
            $this->{$k} = $v;
        }
        return $this;
    }
}
