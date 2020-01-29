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
use \Degami\Basics\Exceptions\BasicException;
use \Degami\Basics\ArrayList;

/**
 * Base element class
 * every form element classes inherits from this class
 *
 * @abstract
 */
abstract class BaseElement
{
    use ToolsTrait;

    /**
     * Element attributes array
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Set html attributes
     *
     * @param string $name  attribute name
     * @param string $value attribute value
     *
     * @return BaseElement
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Set html attributes
     *
     * @param array $attributes attributes array
     *
     * @return BaseElement
     */
    public function setAttributesArray($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get attribute value if present. false on failure
     *
     * @param  string $name attribute name
     * @return string       attribute description
     */
    public function getAttribute($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : false;
    }

    /**
     * Returns the element html attributes string
     *
     * @param  array $reserved_arr array of attributes name that will be
     *                             skipped if present in the attributes
     *                             array
     * @return string               the html attributes string
     */
    public function getAttributes($reserved_arr = ['type','name','id','value'])
    {
        return $this->getAttributesString($this->attributes, $reserved_arr);
    }

    /**
     * Returns the html attributes string
     *
     * @param  array $attributes_arr attributes array
     * @param  array $reserved_arr   array of attributes name that will be
     *                               skipped if present in the attributes array
     * @return string                the html attributes string
     */
    public function getAttributesString(
        $attributes_arr,
        $reserved_arr = ['type','name','id','value']
    ) {
        $attributes = '';
        foreach ($reserved_arr as $key => $reserved) {
            if (isset($attributes_arr[$reserved])) {
                unset($attributes_arr[$reserved]);
            }
        }
        foreach ($attributes_arr as $key => $value) {
            if (!is_string($value) && !is_numeric($value)) {
                continue;
            }
            $value = self::processPlain($value);
            if (trim($value) != '') {
                $value=trim($value);
                $attributes .= " {$key}=\"{$value}\"";
            }
        }
        $attributes = trim($attributes);
        return empty($attributes) ? '' : ' ' . $attributes;
    }

    /**
     * Get attributes array
     *
     * @return array attributes array
     */
    public function getAttributesArray()
    {
        return $this->attributes;
    }

    /**
     * To array
     *
     * @return array array representation for the element properties
     */
    public function toArray()
    {
        $values = get_object_vars($this);
        foreach ($values as $key => $val) {
            $values[$key] = static::toArrayVal($key, $val);
        }
        return $values;
    }

    /**
     * The toArrayVal private method
     *
     * @param  mixed  $key  key
     * @param  mixed  $elem element
     * @param  string $path path
     * @return mixed        element as an array
     */
    private static function toArrayVal($key, $elem, $path = '/')
    {
        if ($key === 'parent') {
            return "-- link to parent --";
        }

        if (is_object($elem) && ($elem instanceof BaseElement)) {
            $elem = $elem->toArray();
        } elseif (is_object($elem) && ($elem instanceof ArrayList)) {
            $elem = 'instanceof ['.get_class($elem).']';
        } elseif (is_array($elem)) {
            foreach ($elem as $k => $val) {
                $elem[$k] = static::toArrayVal($k, $val, $path.$key.'/');
            }
        }

        return $elem;
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
                if (property_exists(get_class($this), $name)) {
                    return $this->{$name};
                }
                // no break
            case 'set':
                $name = $this->PascalCaseToSnakeCase(trim(strtolower(substr($method, 3))));
                $value = is_array($args) ? reset($args) : null;
                if (property_exists(get_class($this), $name)) {
                    $this->{$name} = $value;
                    return $this;
                }
                // no break
            case 'has':
                $name = $this->PascalCaseToSnakeCase(trim(strtolower(substr($method, 3))));
                if (property_exists(get_class($this), $name)) {
                    return true;
                }
                return false;
        }
        throw new BasicException("Invalid method ".get_class($this)."::".$method."(".print_r($args, 1).")");
    }
}
