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
namespace Degami\Basics\Html;

use \Degami\Basics\Traits\ToolsTrait;
use \Degami\Basics\Exceptions\BasicException;

/**
 * A class to render form fields tags
 */
class TagList extends BaseElement implements TagInterface
{
    use ToolsTrait;

    /** @var array tag children */
    protected $children;

    /**
     * Class constructor
     *
     * @param array $options build options
     */
    public function __construct($options = [])
    {
        $this->children = [];
        $this->setClassProperties($options);
    }

    /**
     * Gets html tag string
     *
     * @return string tag html representation
     */
    public function renderTag()
    {
        $out = "";
        foreach ($this->children as $key => $value) {
            if ($value instanceof TagInterface) {
                $out .= $value->renderTag();
            }
        }
        return $out;
    }

    /**
     * Adds a list of child to tag
     *
     * @param  TagInterface[] $children children to add
     * @return TagList
     */
    public function addChildren($children)
    {
        foreach ($children as $child) {
            $this->addChild($child);
        }
        return $this;
    }

    /**
     * Add child to tag
     *
     * @param  TagInterface $child child to add
     * @return TagList
     */
    public function addChild($child)
    {
        if ($child instanceof TagInterface) {
            $this->children[] = $child;
        }
        return $this;
    }

    /**
     * toString magic method
     *
     * @return string the tag html
     */
    public function __toString()
    {
        try {
            return $this->renderTag();
        } catch (BasicException $e) {
            return $e->getMessage()."\n".$e->getTraceAsString();
        }
    }
}
