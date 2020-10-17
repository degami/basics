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

    protected $data = [];

    /**
     * {@inheritdocs}
     */
    public function __get($key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * {@inheritdocs}
     */
    public function __set($key, $value)
    {
        if (property_exists(get_class($this), $key)) {
            throw new BasicException('Cannot define "'.$key.'" property');
        }

        $this->data[$key] = $value;
        return $this;
    }

    /**
     * {@inheritdocs}
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * {@inheritdocs}
     */
    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    /**
     * Set/Get attribute wrapper
     *
     * @param  string $method
     * @param  array  $args
     * @return mixed
     * @throws BasicException
     */
    public function __call($method, $args)
    {
        switch (strtolower(substr($method, 0, 3))) {
            case 'get':
                $name = $this->PascalCaseToSnakeCase(trim(strtolower(substr($method, 3))));
                return $this->{$name} ?? null;
                // no break
            case 'set':
                $name = $this->PascalCaseToSnakeCase(trim(strtolower(substr($method, 3))));
                $this->{$name} = reset($args);
                return $this;
                // no break
            case 'has':
                $name = $this->PascalCaseToSnakeCase(trim(strtolower(substr($method, 3))));
                if (array_key_exists($name, $this->data)) {
                    return true;
                }
                return false;
        }
        throw new BasicException("Invalid method ".get_class($this)."::".$method."(".print_r($args, 1).")");
    }

    /**
     * gets data array
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * sets data array
     *
     * @param mixed $data
     * @return DataElement
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Adds data to the element
     *
     * @param  mixed $data data to add
     * @return DataElement
     */
    public function add($data)
    {
        foreach ((array) $data as $k => $v) {
            $this->{$k} = $v;
        }
        return $this;
    }
}
