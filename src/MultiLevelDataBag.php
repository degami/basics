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

use \Degami\Basics\Exceptions\BasicException;

/**
 * A class to hold data
 *
 * @abstract
 */
abstract class MultiLevelDataBag extends DataBag
{
    /**
     * Element parent
     *
     * @var DataBag
     */
    protected ?DataBag $parent = null;

    /**
     * Class constructor
     *
     * @param mixed $data   data to add
     * @param DataBag $parent element parent object
     */
    public function __construct($data, ?DataBag $parent = null)
    {
        $this->parent = $parent;
        parent::__construct($data);
    }

    /**
     * Gets parent element
     *
     * @return \Degami\Basics\DataBag
     */
    public function getParent() : ?DataBag
    {
        return $this->parent;
    }

    /**
     * Sets parent element
     *
     * @param DataBag $parent
     * @return MultiLevelDataBag
     */
    public function setParent(?DataBag $parent) : self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Sets data
     *
     * @param  string $key   key
     * @param  mixed  $value data to set
     * @return MultiLevelDataBag
     */
    public function __set(string $key, mixed $value) : void
    {
        if ($key == 'dataelement_data' || $key == 'databag_current_position' || $key == 'parent') {
            throw new BasicException('Cannot define "'.$key.'" property');
        }
        $this->checkDataArr();
        $this->dataelement_data[$key] = (is_array($value)) ? new static($value, $this) : $value;
        $this->notifyChange();
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $key key of element to remove
     */
    public function __unset(string $key) : void
    {
        parent::__unset($key);
        $this->notifyChange();
    }

    /**
     * data change notification on the tree
     */
    public function notifyChange() : void
    {
        if ($this->getParent() instanceof MultiLevelDataBag) {
            $this->getParent()->notifyChange();
        } else {
            $this->onChange();
        }
    }

    /**
     * ensures array tree is present as on path parameter
     *
     * @param  string $path      tree path
     * @param  string $delimiter delimiter
     * @return boolean
     */
    public function ensurePath(string $path, string $delimiter = '/') : bool
    {
        if (!is_string($path) || trim($path) == '') {
            return false;
        }
        $path = explode($delimiter, $path);
        $ptr = &$this;
        if (!is_array($path)) {
            $path = [$path];
        }
        foreach ($path as $key => $value) {
            if (trim($value) == '') {
                continue;
            }
            if (!isset($ptr->{$value})) {
                $ptr->{$value} = [];
            }
            $ptr = &$ptr->{$value};
        }
        return true;
    }

    /**
     * data changed event hook
     */
    abstract public function onChange();
}
