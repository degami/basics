<?php
/**
 * Degami Basics
 * PHP Version 7.0
 *
 * @category CMS / Framework
 * @package  Degami\Basics
 * @author   Mirko De Grandis <degami@github.com>
 * @license  MIT https://opensource.org/licenses/mit-license.php
 * @link     https://github.com/degami/sitebase
 */
namespace Degami\Basics;

use \Degami\Basics\Traits\ToolsTrait;

/**
 * A class to hold data
 *
 * @abstract
 */
abstract class DataElement
{
    use ToolsTrait;

    protected $data;

    /**
     * {@inheritdocs}
     */
    public function __get($key)
    {
        return $this->data[$key];
    }

    /**
     * {@inheritdocs}
     */
    public function __set($key, $value)
    {
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
                $this->{$name} = $value;
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
}
